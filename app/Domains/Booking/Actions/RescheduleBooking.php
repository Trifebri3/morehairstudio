<?php

namespace App\Domains\Booking\Actions;

use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Models\BookingItem;
use App\Domains\Booking\Models\BookingStatusHistory;
use App\Domains\Booking\Exceptions\DoubleBookingException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RescheduleBooking
{
    public function execute(Booking $booking, string $newDate, string $newTime, ?int $userId = null): Booking
    {
        return DB::transaction(function () use ($booking, $newDate, $newTime, $userId) {
            $stylistId = $booking->stylist_id;
            
            $item = $booking->items->first();
            if (!$item) {
                throw new \Exception("Booking has no items to reschedule.");
            }

            $duration = $item->duration;
            $startTime = Carbon::createFromFormat('H:i', $newTime);
            $endTime = $startTime->copy()->addMinutes($duration);

            // Double booking check, excluding this specific booking
            $isOverlap = Booking::where('stylist_id', $stylistId)
                ->where('id', '!=', $booking->id)
                ->where('booking_date', $newDate)
                ->whereNotIn('status', ['cancelled', 'expired'])
                ->whereHas('items', function ($q) use ($startTime, $endTime) {
                    $q->where(function ($sub) use ($startTime, $endTime) {
                        $sub->where('start_time', '<', $endTime->format('H:i:s'))
                            ->where('end_time', '>', $startTime->format('H:i:s'));
                    });
                })->exists();

            if ($isOverlap) {
                throw new DoubleBookingException("Selected time slot is already booked for this stylist.");
            }

            // Update booking date & reset status to confirmed
            $booking->update([
                'booking_date' => $newDate,
                'status' => 'confirmed'
            ]);

            // Update item times
            $item->update([
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s'),
            ]);

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'status' => 'confirmed',
                'changed_by' => $userId,
                'reason' => "Booking rescheduled to {$newDate} at {$newTime}."
            ]);

            event(new \App\Domains\Booking\Events\BookingRescheduled($booking));

            return $booking;
        });
    }
}
