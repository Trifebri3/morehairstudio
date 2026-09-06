<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Domains\Booking\Models\Booking;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-complete active treatments based on check-in time and service duration (runs every minute)
Artisan::command('bookings:auto-complete', function () {
    $count = Booking::autoCompleteDueBookings();
    $this->info("Auto-completed {$count} due bookings successfully.");
})->purpose('Auto-complete treatments whose service duration has elapsed after check-in');

Schedule::call(function () {
    Booking::autoCompleteDueBookings();
})->everyMinute();

// Auto-cancel / expire no-show bookings that missed the outlet's configured check-in grace period (runs every minute)
Artisan::command('bookings:auto-expire', function () {
    $count = Booking::autoExpireNoShows();
    $this->info("Auto-expired {$count} no-show bookings past check-in grace period. Slots freed.");
})->purpose('Auto-expire bookings where customer did not check in within outlet grace period');

Schedule::call(function () {
    Booking::autoExpireNoShows();
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
