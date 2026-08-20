<?php

namespace App\Domains\Booking\Actions;

use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Models\BookingStatusHistory;
use Exception;

class CheckInBooking
{
    public function execute(Booking $booking, int $outletId, ?int $userId = null): Booking
    {
        if ($booking->outlet_id !== $outletId) {
            throw new Exception("This booking belongs to another outlet.");
        }

        if (in_array($booking->status, ['checked_in', 'cancelled', 'expired', 'completed'])) {
            if ($booking->status === 'checked_in') {
                throw new Exception("Pemesanan ini sudah melakukan check-in sebelumnya.");
            }
            throw new Exception("Pemesanan tidak dapat check-in karena berstatus {$booking->status}.");
        }

        // Check if booking scheduled date and time are in the past
        $firstItem = $booking->items->first();
        if ($firstItem) {
            $bookingDateTime = \Carbon\Carbon::parse($booking->booking_date->toDateString() . ' ' . $firstItem->start_time);
            if ($bookingDateTime->isPast()) {
                throw new Exception("Pemesanan ini sudah kedaluwarsa karena jadwal kunjungan telah terlewati.");
            }
        }

        $booking->update(['status' => 'checked_in']);

        BookingStatusHistory::create([
            'booking_id' => $booking->id,
            'status' => 'checked_in',
            'changed_by' => $userId,
            'reason' => 'Customer checked in at outlet.'
        ]);

        event(new \App\Domains\Booking\Events\BookingCheckedIn($booking));

        return $booking;
    }
}
