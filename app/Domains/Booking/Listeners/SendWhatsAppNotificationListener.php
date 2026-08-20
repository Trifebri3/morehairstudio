<?php

namespace App\Domains\Booking\Listeners;

use App\Domains\Booking\Events\BookingCreated;
use App\Domains\Booking\Events\BookingConfirmed;
use App\Domains\Booking\Events\BookingRescheduled;
use App\Domains\Booking\Events\BookingCancelled;
use App\Domains\Booking\Events\BookingCheckedIn;
use App\Domains\Booking\Events\BookingExpired;
use App\Domains\Booking\Events\BookingCompleted;

use App\Notifications\BookingCreatedNotification;
use App\Notifications\BookingConfirmedNotification;
use App\Notifications\BookingExpiredNotification;
use App\Notifications\BookingCancelledNotification;
use App\Notifications\BookingRescheduledNotification;
use App\Notifications\BookingCompletedNotification;
use App\Notifications\WalkInBookingNotification;

class SendWhatsAppNotificationListener
{
    public function handleBookingCreated(BookingCreated $event): void
    {
        if ($event->booking->source === 'walk-in') {
            $event->booking->customer->notify(new WalkInBookingNotification($event->booking));
        } else {
            if (config('whatsapp.notifications.booking_confirmation', true)) {
                $event->booking->customer->notify(new BookingCreatedNotification($event->booking));
            }
        }
    }

    public function handleBookingConfirmed(BookingConfirmed $event): void
    {
        if (config('whatsapp.notifications.booking_confirmation', true)) {
            $event->booking->customer->notify(new BookingConfirmedNotification($event->booking));
        }
    }

    public function handleBookingCancelled(BookingCancelled $event): void
    {
        if (config('whatsapp.notifications.booking_cancelled', true)) {
            $event->booking->customer->notify(new BookingCancelledNotification($event->booking));
        }
    }

    public function handleBookingRescheduled(BookingRescheduled $event): void
    {
        $event->booking->customer->notify(new BookingRescheduledNotification($event->booking));
    }

    public function handleBookingCheckedIn(BookingCheckedIn $event): void
    {
        // Logic for CheckedIn if required
    }

    public function handleBookingExpired(BookingExpired $event): void
    {
        if (config('whatsapp.notifications.booking_expired', true)) {
            $event->booking->customer->notify(new BookingExpiredNotification($event->booking));
        }
    }

    public function handleBookingCompleted(BookingCompleted $event): void
    {
        if (config('whatsapp.notifications.booking_completed', true)) {
            $event->booking->customer->notify(new BookingCompletedNotification($event->booking));
        }
    }

    /**
     * Register listeners inside event manager.
     */
    public function subscribe($events): array
    {
        return [
            BookingCreated::class => 'handleBookingCreated',
            BookingConfirmed::class => 'handleBookingConfirmed',
            BookingCancelled::class => 'handleBookingCancelled',
            BookingRescheduled::class => 'handleBookingRescheduled',
            BookingCheckedIn::class => 'handleBookingCheckedIn',
            BookingExpired::class => 'handleBookingExpired',
            BookingCompleted::class => 'handleBookingCompleted',
        ];
    }
}
