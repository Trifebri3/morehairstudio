<?php

namespace App\Domains\Booking\Services;

use App\Domains\Outlet\Models\Outlet;
use App\Domains\Stylist\Models\Stylist;
use App\Domains\Service\Models\Service;
use App\Domains\Booking\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AvailabilityService
{
    /**
     * Get available time slots for a specific outlet, stylist, service, and date.
     */
    public function getAvailableSlots(int $outletId, int $stylistId, int $serviceId, string $dateString, bool $isWalkIn = false): array
    {
        $date = Carbon::parse($dateString);
        $dayOfWeek = $date->dayOfWeek; // 0 = Sunday, 1 = Monday, ..., 6 = Saturday

        // Get Stylist with schedule for that day
        $stylist = Stylist::with(['schedules' => function ($q) use ($dayOfWeek) {
            $q->where('day_of_week', $dayOfWeek);
        }])->findOrFail($stylistId);

        $schedule = $stylist->schedules->first();
        if (!$schedule || !$schedule->is_working) {
            return []; // Stylist is not working on this day
        }

        // Get Service price and duration (from outlet overrides or fallback default)
        $outletService = DB::table('outlet_services')
            ->where('outlet_id', $outletId)
            ->where('service_id', $serviceId)
            ->first();

        if ($outletService && !$outletService->is_active) {
            return []; // Service is disabled at this outlet
        }

        $duration = $outletService ? ($outletService->duration ?? 45) : 45;

        // Auto-expire past bookings that missed the check-in grace period
        $outlet = Outlet::find($outletId);
        $graceActive = $outlet ? (bool)($outlet->checkin_grace_period_active ?? true) : true;
        $graceMinutes = $outlet ? ($outlet->checkin_grace_period_minutes ?? 15) : 15;

        if ($graceActive) {
            $now = Carbon::now();
            $pendingBookings = Booking::where('outlet_id', $outletId)
                ->where('status', 'confirmed')
                ->where(function ($q) use ($now) {
                    $q->whereDate('booking_date', '<', $now->toDateString())
                      ->orWhere(function ($sub) use ($now) {
                          $sub->whereDate('booking_date', $now->toDateString());
                      });
                })
                ->with('items')
                ->get();

            foreach ($pendingBookings as $pb) {
                $firstItem = $pb->items->first();
                if ($firstItem) {
                    $pbStart = Carbon::parse($pb->booking_date->toDateString() . ' ' . $firstItem->start_time);
                    if ($pbStart->addMinutes($graceMinutes)->isPast()) {
                        $pb->update(['status' => 'expired']);

                        \App\Domains\Booking\Models\BookingStatusHistory::create([
                            'booking_id' => $pb->id,
                            'status' => 'expired',
                            'reason' => "Booking auto-expired: missed check-in grace period of {$graceMinutes} minutes."
                        ]);
                    }
                }
            }
        }

        // Get existing active bookings for this stylist on this date
        $existingBookings = Booking::where('stylist_id', $stylistId)
            ->whereDate('booking_date', $dateString)
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->with('items')
            ->get();

        $startTime = Carbon::createFromFormat('H:i:s', $schedule->start_time);
        $endTime = Carbon::createFromFormat('H:i:s', $schedule->end_time);

        $breakStart = $schedule->break_start ? Carbon::createFromFormat('H:i:s', $schedule->break_start) : null;
        $breakEnd = $schedule->break_end ? Carbon::createFromFormat('H:i:s', $schedule->break_end) : null;

        $slots = [];
        $current = $startTime->copy();

        $outlet = Outlet::find($outletId);
        $leadTimeHours = $outlet ? $outlet->booking_lead_time_hours : 1;

        // Increment slots every 30 minutes
        while ($current->copy()->addMinutes($duration)->lte($endTime)) {
            $slotStart = $current->copy();
            $slotEnd = $current->copy()->addMinutes($duration);
            $isAvailable = true;

            // Check if slot falls in break time
            if ($breakStart && $breakEnd) {
                if ($slotStart->lt($breakEnd) && $slotEnd->gt($breakStart)) {
                    $isAvailable = false;
                }
            }

            // Check if slot overlaps with existing bookings
            if ($isAvailable) {
                foreach ($existingBookings as $booking) {
                    foreach ($booking->items as $item) {
                        $bookedStart = Carbon::createFromFormat('H:i:s', $item->start_time);
                        $bookedEnd = Carbon::createFromFormat('H:i:s', $item->end_time);

                        if ($slotStart->lt($bookedEnd) && $slotEnd->gt($bookedStart)) {
                            $isAvailable = false;
                            break 2;
                        }
                    }
                }
            }

            // Check past slots and booking lead time
            if ($isAvailable) {
                $slotStartDateTime = Carbon::parse($dateString . ' ' . $slotStart->format('H:i:s'));
                
                if ($isWalkIn) {
                    // Walk-in is immediately available at this time
                    if ($slotStartDateTime->lt(Carbon::now())) {
                        $isAvailable = false;
                    }
                } else {
                    // Online booking requires lead time (H-X Hours)
                    if ($slotStartDateTime->lt(Carbon::now()->addHours($leadTimeHours))) {
                        $isAvailable = false;
                    }
                }
            }

            if ($isAvailable) {
                $slots[] = [
                    'time' => $slotStart->format('H:i'),
                    'label' => $slotStart->format('H:i') . ' - ' . $slotEnd->format('H:i'),
                    'end_time' => $slotEnd->format('H:i')
                ];
            }

            $current->addMinutes($duration);
        }

        return $slots;
    }
}
