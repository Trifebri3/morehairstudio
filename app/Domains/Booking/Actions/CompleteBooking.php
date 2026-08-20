<?php

namespace App\Domains\Booking\Actions;

use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Models\BookingStatusHistory;

class CompleteBooking
{
    public function execute(Booking $booking, ?int $userId = null): Booking
    {
        $booking->update(['status' => 'completed']);

        // Auto pay pending payments at completion
        foreach ($booking->payments as $payment) {
            if ($payment->status === 'pending') {
                $payment->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }
        }

        BookingStatusHistory::create([
            'booking_id' => $booking->id,
            'status' => 'completed',
            'changed_by' => $userId,
            'reason' => 'Booking marked as completed by stylist/staff.'
        ]);

        event(new \App\Domains\Booking\Events\BookingCompleted($booking));

        return $booking;
    }
}
