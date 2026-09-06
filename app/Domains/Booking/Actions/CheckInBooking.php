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

        if ($booking->shouldAutoExpire()) {
            $booking->autoExpireIfDue();
        }

        if (in_array($booking->status, ['checked_in', 'cancelled', 'expired', 'completed'])) {
            if ($booking->status === 'checked_in') {
                throw new Exception("Pemesanan ini sudah melakukan check-in sebelumnya.");
            }
            if ($booking->status === 'expired') {
                $outlet = $booking->outlet ?? \App\Domains\Outlet\Models\Outlet::find($booking->outlet_id);
                $graceMinutes = $outlet ? (int)($outlet->checkin_grace_period_minutes ?? 15) : 15;
                throw new Exception("Pemesanan ini sudah hangus karena melewati batas waktu toleransi check-in ({$graceMinutes} menit). Slot telah dibuka kembali.");
            }
            throw new Exception("Pemesanan tidak dapat check-in karena berstatus {$booking->status}.");
        }

        // Start service timing based on check-in time and chosen service duration
        $booking->startServiceTiming();
        $booking->status = 'checked_in';
        $booking->save();

        $duration = $booking->service_duration_minutes;
        $endTime = $booking->service_end_at ? $booking->service_end_at->format('H:i') : '-';

        BookingStatusHistory::create([
            'booking_id' => $booking->id,
            'status' => 'checked_in',
            'changed_by' => $userId,
            'reason' => "Customer check-in di outlet. Layanan dimulai (Durasi: {$duration} menit, auto-selesai estimasi: {$endTime} WIB)."
        ]);

        event(new \App\Domains\Booking\Events\BookingCheckedIn($booking));

        return $booking;
    }
}
