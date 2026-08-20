<?php

namespace App\Domains\Booking\Actions;

use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Models\BookingStatusHistory;

class CancelBooking
{
    public function execute(Booking $booking, ?int $userId = null, ?string $reason = null): Booking
    {
        $booking->update(['status' => 'cancelled']);

        BookingStatusHistory::create([
            'booking_id' => $booking->id,
            'status' => 'cancelled',
            'changed_by' => $userId,
            'reason' => $reason ?? 'Booking cancelled.'
        ]);

        event(new \App\Domains\Booking\Events\BookingCancelled($booking));

        return $booking;
    }
}
