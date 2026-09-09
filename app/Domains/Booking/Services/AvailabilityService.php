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
        if ($schedule && !$schedule->is_working) {
            return []; // Stylist is explicitly set to not working on this day
        }

        // Get Service price and duration (from outlet overrides or fallback default)
        $outletService = DB::table('outlet_services')
            ->where('outlet_id', $outletId)
            ->where('service_id', $serviceId)
            ->first();

        if ($outletService && !$outletService->is_active) {
            return []; // Service is disabled at this outlet
        }

        $service = Service::find($serviceId);
        $duration = ($outletService && $outletService->duration) ? (int)$outletService->duration : ($service ? (int)$service->default_duration : 45);

        // Auto-expire no-show bookings that missed the check-in grace period
        Booking::autoExpireNoShows($outletId);

        // Get existing active bookings for this stylist on this date
        $existingBookings = Booking::where('stylist_id', $stylistId)
            ->whereDate('booking_date', $dateString)
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->with('items')
            ->get();

        $outlet = Outlet::find($outletId);
        $dayName = strtolower($date->format('l'));
        $outletHours = ($outlet && isset($outlet->opening_hours[$dayName])) ? $outlet->opening_hours[$dayName] : null;
        $isOutletOpen = $outletHours ? (bool)($outletHours['is_open'] ?? true) : true;
        if (!$isOutletOpen) {
            return [];
        }

        $defaultOpen = ($outletHours && !empty($outletHours['open'])) ? $outletHours['open'] . ':00' : '10:00:00';
        $defaultClose = ($outletHours && !empty($outletHours['close'])) ? $outletHours['close'] . ':00' : '20:00:00';

        $startTimeStr = ($schedule && $schedule->start_time) ? $schedule->start_time : $defaultOpen;
        $endTimeStr = ($schedule && $schedule->end_time) ? $schedule->end_time : $defaultClose;

        $leadTimeHours = $outlet ? $outlet->booking_lead_time_hours : 1;

        // Pre-parse booked intervals as minute integers from midnight for lightning-fast collision check
        $bookedIntervals = [];
        foreach ($existingBookings as $booking) {
            foreach ($booking->items as $item) {
                $startParts = explode(':', $item->start_time);
                $endParts = explode(':', $item->end_time);
                $bStartMin = ((int)$startParts[0] * 60) + (int)($startParts[1] ?? 0);
                $bEndMin = ((int)$endParts[0] * 60) + (int)($endParts[1] ?? 0);
                $bookedIntervals[] = [$bStartMin, $bEndMin];
            }
        }

        $startParts = explode(':', $startTimeStr);
        $endParts = explode(':', $endTimeStr);
        $startMin = ((int)$startParts[0] * 60) + (int)($startParts[1] ?? 0);
        $endMin = ((int)$endParts[0] * 60) + (int)($endParts[1] ?? 0);

        $now = Carbon::now();
        $isToday = ($dateString === $now->toDateString());
        $nowMin = ($now->hour * 60) + $now->minute;
        $walkInMinLimit = $nowMin - 15;
        $leadTimeMinLimit = $nowMin + 15; // Online booking lead time strictly 15 minutes

        // Sesi treatment dan haircut diset 1 jam (60 menit) per jam bulat
        $sessionDuration = max(60, $duration);

        // Generate kandidat jam per jam bulat (misal 10:00, 11:00, 12:00, 13:00, dst.)
        $startHour = (int)ceil($startMin / 60);
        $endHour = (int)floor($endMin / 60);

        $candidateMinutes = [];
        for ($h = $startHour; ($h * 60) + $sessionDuration <= $endMin; $h++) {
            $candidateMinutes[] = $h * 60;
        }

        $slots = [];
        $firstFound = false;

        foreach ($candidateMinutes as $slotStartMin) {
            $slotEndMin = $slotStartMin + $sessionDuration;

            // Pastikan dalam batas jam kerja
            if ($slotStartMin < $startMin || $slotEndMin > $endMin) {
                continue;
            }

            // Aturan lead time jika booking untuk hari ini
            if ($isToday) {
                if ($isWalkIn) {
                    if ($slotStartMin < $walkInMinLimit) {
                        continue;
                    }
                } else {
                    // Online booking wajib minimal 15 menit sebelum jam mulai sesi
                    // Misal jam 11:09, batas minimal adalah 11:24. Sesi 11:00 tertolak, sesi 12:00 tersedia.
                    if ($slotStartMin < $leadTimeMinLimit) {
                        continue;
                    }
                }
            }

            // Cek bentrok dengan booking aktif yang sudah ada
            $hasOverlap = false;
            foreach ($bookedIntervals as [$bStart, $bEnd]) {
                if ($slotStartMin < $bEnd && $slotEndMin > $bStart) {
                    $hasOverlap = true;
                    break;
                }
            }

            if ($hasOverlap) {
                continue;
            }

            // Slot pertama yang tersedia setelah lead time adalah Rekomendasi Tercepat
            $isPriority = false;
            $badge = '';

            if (!$firstFound) {
                $isPriority = true;
                $badge = 'Paling Cepat';
                $firstFound = true;
            }

            $hStart = str_pad((string)floor($slotStartMin / 60), 2, '0', STR_PAD_LEFT);
            $mStart = str_pad((string)($slotStartMin % 60), 2, '0', STR_PAD_LEFT);
            $hEnd = str_pad((string)floor($slotEndMin / 60), 2, '0', STR_PAD_LEFT);
            $mEnd = str_pad((string)($slotEndMin % 60), 2, '0', STR_PAD_LEFT);

            $timeStr = "{$hStart}:{$mStart}";
            $endTimeStrFormatted = "{$hEnd}:{$mEnd}";

            $slots[] = [
                'time' => $timeStr,
                'label' => "{$timeStr} - {$endTimeStrFormatted}",
                'end_time' => $endTimeStrFormatted,
                'is_priority' => $isPriority,
                'badge' => $badge,
            ];
        }

        return $slots;
    }

    /**
     * Check if a specific custom start time and duration is available for a stylist.
     */
    public function isIntervalAvailable(int $outletId, int $stylistId, int $serviceId, string $dateString, string $timeString, bool $isWalkIn = false): array
    {
        $date = Carbon::parse($dateString);
        $dayOfWeek = $date->dayOfWeek;

        $stylist = Stylist::with(['schedules' => function ($q) use ($dayOfWeek) {
            $q->where('day_of_week', $dayOfWeek);
        }])->find($stylistId);

        if (!$stylist) {
            return ['available' => false, 'message' => 'Stylist tidak ditemukan.'];
        }

        $schedule = $stylist->schedules->first();
        if (!$schedule || !$schedule->is_working) {
            return ['available' => false, 'message' => "Stylist {$stylist->name} tidak bertugas pada hari yang dipilih."];
        }

        // Resolve service duration
        $outletService = DB::table('outlet_services')
            ->where('outlet_id', $outletId)
            ->where('service_id', $serviceId)
            ->first();

        $duration = $outletService ? ($outletService->duration ?? null) : null;
        if ($duration === null) {
            $service = Service::find($serviceId);
            $duration = $service ? $service->default_duration : 45;
        }

        try {
            $startTime = Carbon::createFromFormat('H:i', $timeString);
        } catch (\Exception $e) {
            return ['available' => false, 'message' => 'Format jam tidak valid (gunakan format JJ:MM).'];
        }

        $endTime = $startTime->copy()->addMinutes($duration);

        $workStart = Carbon::createFromFormat('H:i:s', $schedule->start_time);
        $workEnd = Carbon::createFromFormat('H:i:s', $schedule->end_time);

        // Check working hours
        if ($startTime->lt($workStart) || $endTime->gt($workEnd)) {
            return [
                'available' => false,
                'message' => "Jam operasional stylist {$stylist->name} adalah {$workStart->format('H:i')} - {$workEnd->format('H:i')} WIB."
            ];
        }


        // Auto-expire no-show bookings that missed the check-in grace period
        Booking::autoExpireNoShows($outletId);

        // Check existing bookings overlap
        $existingBookings = Booking::where('stylist_id', $stylistId)
            ->whereDate('booking_date', $dateString)
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->with('items')
            ->get();

        foreach ($existingBookings as $booking) {
            foreach ($booking->items as $item) {
                $bStart = Carbon::createFromFormat('H:i:s', $item->start_time);
                $bEnd = Carbon::createFromFormat('H:i:s', $item->end_time);

                if ($startTime->lt($bEnd) && $endTime->gt($bStart)) {
                    return [
                        'available' => false,
                        'message' => "Stylist {$stylist->name} sudah memiliki jadwal sesi jam {$bStart->format('H:i')} - {$bEnd->format('H:i')} WIB. Sesi {$startTime->format('H:i')} - {$endTime->format('H:i')} bertabrakan."
                    ];
                }
            }
        }

        // Check past time and lead time (strictly 15 minutes for online booking)
        $slotStartDateTime = Carbon::parse($dateString . ' ' . $startTime->format('H:i:s'));

        if ($isWalkIn) {
            if ($slotStartDateTime->lt(Carbon::now()->subMinutes(15))) {
                return ['available' => false, 'message' => 'Waktu sesi walk-in lebih dari 15 menit yang lalu.'];
            }
        } else {
            $minBookingTime = Carbon::now()->addMinutes(15);
            if ($slotStartDateTime->lt($minBookingTime)) {
                $maxBookingTime = $startTime->copy()->subMinutes(15)->format('H:i');
                return [
                    'available' => false,
                    'message' => "Pemesanan online minimal dilakukan 15 menit sebelum jam mulai. Untuk sesi jam {$startTime->format('H:i')} WIB, maksimal booking sebelum jam {$maxBookingTime} WIB (minimal jam sesi saat ini {$minBookingTime->format('H:i')} WIB)."
                ];
            }
        }

        return [
            'available' => true,
            'duration' => $duration,
            'start_time' => $startTime->format('H:i'),
            'end_time' => $endTime->format('H:i'),
            'message' => "Sesi {$startTime->format('H:i')} - {$endTime->format('H:i')} WIB ({$duration} Menit) tersedia!"
        ];
    }
}
