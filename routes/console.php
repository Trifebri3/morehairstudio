<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Domains\Booking\Models\Booking;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-cancellation job for no-show bookings past 15 minutes
Schedule::call(function () {
    $today = Carbon::today()->toDateString();
    
    $bookings = Booking::whereDate('booking_date', $today)
        ->whereIn('status', ['pending', 'confirmed'])
        ->with('items')
        ->get();

    foreach ($bookings as $booking) {
        $item = $booking->items->first();
        if ($item) {
            $bookingTime = Carbon::parse($booking->booking_date->format('Y-m-d') . ' ' . $item->start_time);
            
            // Cancel booking if customer has not arrived 15 minutes after start time
            if (Carbon::now()->gt($bookingTime->addMinutes(15))) {
                $booking->update([
                    'status' => 'expired'
                ]);

                $booking->statusHistories()->create([
                    'status' => 'expired',
                    'reason' => 'Auto-expired: No-show 15 minutes past schedule.'
                ]);

                event(new \App\Domains\Booking\Events\BookingExpired($booking));
            }
        }
    }
})->everyMinute();

// Hourly Booking Reminder Job
Schedule::call(function () {
    $reminderMinutes = config('booking.reminder_before_minutes', 1440); // default to 24 hours (1440 mins)

    $bookings = Booking::where('status', 'confirmed')
        ->where('booking_date', '>=', Carbon::today())
        ->with('items')
        ->get();

    foreach ($bookings as $booking) {
        $item = $booking->items->first();
        if (!$item) {
            continue;
        }

        $bookingTime = Carbon::parse($booking->booking_date->format('Y-m-d') . ' ' . $item->start_time);
        $diff = Carbon::now()->diffInMinutes($bookingTime, false);

        // If the booking is in the future and within the reminder window
        if ($diff > 0 && $diff <= $reminderMinutes) {
            // Check if reminder was already sent
            $alreadySent = Illuminate\Support\Facades\DB::table('whatsapp_messages')
                ->where('booking_id', $booking->id)
                ->where('template_name', 'booking_reminder')
                ->exists();

            if (!$alreadySent) {
                $booking->customer->notify(new \App\Notifications\BookingReminderNotification($booking));
            }
        }
    }
})->hourly();

// Check-in Reminder Job (runs every 10 minutes)
Schedule::call(function () {
    $bookings = Booking::where('status', 'confirmed')
        ->whereDate('booking_date', Carbon::today())
        ->with('items')
        ->get();

    foreach ($bookings as $booking) {
        $item = $booking->items->first();
        if (!$item) {
            continue;
        }

        $bookingTime = Carbon::parse($booking->booking_date->format('Y-m-d') . ' ' . $item->start_time);
        $diff = Carbon::now()->diffInMinutes($bookingTime, false);

        // Send check-in reminder 15 minutes or less before booking time
        if ($diff > 0 && $diff <= 15) {
            $alreadySent = Illuminate\Support\Facades\DB::table('whatsapp_messages')
                ->where('booking_id', $booking->id)
                ->where('template_name', 'booking_check_in_reminder')
                ->exists();

            if (!$alreadySent) {
                $booking->customer->notify(new \App\Notifications\BookingCheckInReminderNotification($booking));
            }
        }
    }
})->everyTenMinutes();
