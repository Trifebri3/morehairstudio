<?php

namespace App\Livewire\Tablet;

use Livewire\Component;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Actions\CompleteBooking;
use App\Domains\Booking\Models\BookingStatusHistory;
use Carbon\Carbon;

class Queue extends Component
{
    public $successMessage = null;

    public function startService($bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        $booking->update(['status' => 'in_progress']);

        BookingStatusHistory::create([
            'booking_id' => $booking->id,
            'status' => 'in_progress',
            'reason' => 'Stylist started treatment.'
        ]);

        $this->successMessage = "Treatment dimulai untuk booking: {$booking->booking_code}.";
    }

    public function completeService($bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        $complete = new CompleteBooking();
        $complete->execute($booking);

        $this->successMessage = "Treatment selesai! Booking {$booking->booking_code} ditandai completed.";
    }

    public function render()
    {
        $today = Carbon::today()->toDateString();

        $tabletOutletId = session('tablet_outlet_id', 1);
        // Load today's active appointments
        $bookings = Booking::where('outlet_id', $tabletOutletId)
            ->where('booking_date', $today)
            ->whereIn('status', ['pending', 'confirmed', 'checked_in', 'in_progress', 'completed'])
            ->with(['customer', 'stylist', 'items.service'])
            ->get();

        return view('livewire.tablet.queue', compact('bookings'))->layout('layouts.tablet');
    }
}
