<?php

namespace App\Livewire\Outlet;

use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Models\BookingStatusHistory;
use App\Domains\Booking\Actions\CompleteBooking;

class Bookings extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    protected $updatesQueryString = ['search', 'statusFilter'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updateStatus($bookingId, $newStatus)
    {
        $booking = Booking::findOrFail($bookingId);
        
        if ($newStatus === 'completed') {
            $complete = new CompleteBooking();
            $complete->execute($booking);
        } else {
            $booking->update(['status' => $newStatus]);
            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'status' => $newStatus,
                'reason' => 'Status updated via Outlet Admin Dashboard.'
            ]);
        }

        session()->flash('message', "Status booking {$booking->booking_code} berhasil diubah ke {$newStatus}.");
    }

    public function render()
    {
        $outletId = auth()->user()->outlet_id ?? 1;

        $bookings = Booking::where('outlet_id', $outletId)
            ->where(function ($q) {
                $q->where('booking_code', 'like', '%' . $this->search . '%')
                  ->orWhereHas('customer', function ($c) {
                      $c->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%');
                  });
            })
            ->when($this->statusFilter, function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->with(['customer', 'stylist', 'items.service'])
            ->latest()
            ->paginate(10);

        return view('livewire.outlet.bookings', compact('bookings'))->layout('layouts.admin');
    }
}
