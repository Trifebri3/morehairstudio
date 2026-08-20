<?php

namespace App\Livewire\Tablet;

use App\Livewire\Booking\BookingWizard;
use App\Domains\Booking\Actions\CreateBooking;

class WalkIn extends BookingWizard
{
    public $isTablet = true;
    public $isWalkIn = true;

    public function mount()
    {
        parent::mount();
        if (request()->has('tablet_outlet_id')) {
            session(['tablet_outlet_id' => (int) request()->query('tablet_outlet_id')]);
        }
        $this->selectedOutletId = session('tablet_outlet_id', 1);
        $this->currentStep = 1; // Start at Step 1 (Pilih Layanan since outlet is pre-locked)
        $this->paymentMethod = 'manual'; // Default pay at outlet
    }

    public function confirmBooking()
    {
        $createBooking = new CreateBooking();
        
        $booking = $createBooking->execute([
            'phone' => $this->phone,
            'customer_name' => $this->customerName,
            'email' => $this->email,
            'birth_date' => $this->birthDate,
            'gender' => $this->gender,
            'outlet_id' => $this->selectedOutletId,
            'service_id' => $this->selectedServiceId,
            'stylist_id' => $this->selectedStylistId,
            'booking_date' => $this->selectedDate,
            'booking_time' => $this->selectedTime,
            'promo_code' => $this->promoCode,
            'payment_method' => $this->paymentMethod,
            'notes' => $this->notes,
            'source' => 'walk_in' // Forced walk_in source
        ]);

        return redirect()->route('tablet.check-in')->with('status', "Walk-In booking berhasil dibuat: {$booking->booking_code}");
    }

    public function render()
    {
        // Re-use wizard view with tablet layout structure
        return parent::render()->layout('layouts.tablet');
    }
}
