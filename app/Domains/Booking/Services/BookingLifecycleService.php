<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\Events\BookingCompleted;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Models\BookingStatusHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BookingLifecycleService
{
    /**
     * Run all automated booking lifecycle updates:
     * 1. Expire un-checked-in bookings after 15 minutes grace period.
     * 2. Complete checked-in bookings after their service duration has elapsed.
     */
    public static function runLifecycle(?int $outletId = null): void
    {
        self::checkAndExpireOverdueBookings($outletId);
        self::checkAndCompleteFinishedBookings($outletId);
    }

    /**
     * If an online or advance booking is 15 minutes past its start time
     * without the customer checking in, automatically mark it as expired.
     * This immediately frees up the slot for walk-in and other customers.
     */
    public static function checkAndExpireOverdueBookings(?int $outletId = null): int
    {
        $now = Carbon::now();
        $query = Booking::whereIn('status', ['pending', 'confirmed'])
            ->with('items');

        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }

        $bookings = $query->get();
        $expiredCount = 0;

        foreach ($bookings as $b) {
            $firstItem = $b->items->first();
            if ($firstItem && $firstItem->start_time) {
                $sessionStart = Carbon::parse($b->booking_date->toDateString().' '.$firstItem->start_time);

                // 15-minute grace period
                if ($sessionStart->copy()->addMinutes(15)->lt($now)) {
                    $b->update(['status' => 'expired']);

                    BookingStatusHistory::create([
                        'booking_id' => $b->id,
                        'status' => 'expired',
                        'reason' => 'Otomatis kedaluwarsa: Pelanggan belum check-in setelah batas toleransi 15 menit dari jam sesi.',
                    ]);

                    $expiredCount++;
                }
            }
        }

        return $expiredCount;
    }

    /**
     * Once a customer checks in, the system automatically marks the booking
     * as completed when the total ordered service duration has elapsed.
     * (e.g., haircut 45 min -> automatically completed 45 min after check-in).
     */
    public static function checkAndCompleteFinishedBookings(?int $outletId = null): int
    {
        $now = Carbon::now();
        $query = Booking::whereIn('status', ['checked_in', 'in_service'])
            ->with(['items', 'statusHistories']);

        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }

        $bookings = $query->get();
        $completedCount = 0;

        foreach ($bookings as $b) {
            // Find check-in timestamp
            $checkInHistory = $b->statusHistories
                ->where('status', 'checked_in')
                ->sortByDesc('created_at')
                ->first();

            $checkInTime = $checkInHistory ? Carbon::parse($checkInHistory->created_at) : Carbon::parse($b->updated_at);

            // Total duration in minutes (sum of items or default 45 mins)
            $totalDuration = (int) $b->items->sum('duration');
            if ($totalDuration <= 0) {
                $totalDuration = 45;
            }

            $sessionEnd = $checkInTime->copy()->addMinutes($totalDuration);

            if ($sessionEnd->lte($now)) {
                $b->update(['status' => 'completed']);

                BookingStatusHistory::create([
                    'booking_id' => $b->id,
                    'status' => 'completed',
                    'reason' => "Otomatis selesai: Durasi pengerjaan {$totalDuration} menit telah tercapai.",
                ]);

                if (class_exists(BookingCompleted::class)) {
                    try {
                        event(new BookingCompleted($b));
                    } catch (\Exception $e) {
                        Log::warning('BookingCompleted event failed: '.$e->getMessage());
                    }
                }

                $completedCount++;
            }
        }

        return $completedCount;
    }
}
