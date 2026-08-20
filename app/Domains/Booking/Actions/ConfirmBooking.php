<?php

namespace App\Domains\Booking\Actions;

use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Models\BookingStatusHistory;

class ConfirmBooking
{
    public function execute(Booking $booking, ?int $userId = null, ?string $reason = null): Booking
    {
        $booking->update(['status' => 'confirmed']);

        BookingStatusHistory::create([
            'booking_id' => $booking->id,
            'status' => 'confirmed',
            'changed_by' => $userId,
            'reason' => $reason ?? 'Booking confirmed.'
        ]);

        event(new \App\Domains\Booking\Events\BookingConfirmed($booking));

        return $booking;
    }
}
