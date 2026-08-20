<?php

namespace App\Livewire\Tablet;

use Livewire\Component;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Actions\CompleteBooking;
use App\Domains\Payment\Models\Payment;
use App\Domains\Outlet\Models\Outlet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Styscreen extends Component
{
    // Auth properties
    public $email = '';
    public $password = '';
    public $loginError = null;

    // Search and filters
    public $searchQuery = '';
    public $selectedBookingId = null;
    public $paymentSuccess = null;
    public $errorMessage = null;

    // Payment Form
    public $paymentMethod = 'edc';
    public $transactionReference = '';

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], true)) {
            $user = Auth::user();
            if (in_array($user->role, ['outlet_admin', 'super_admin'])) {
                $outletId = $user->outlet_id ?? 1;
                session(['tablet_outlet_id' => $outletId]);
                $this->loginError = null;
                $this->email = '';
                $this->password = '';
            } else {
                Auth::logout();
                $this->loginError = 'Akses ditolak. Hanya Admin Outlet atau Super Admin yang dapat mengakses layar ini.';
            }
        } else {
            $this->loginError = 'Email atau password salah.';
        }
    }

    public function logout()
    {
        Auth::logout();
        session()->forget('tablet_outlet_id');
        return redirect()->route('tablet.styscreen');
    }

    public function selectBookingForPayment($bookingId)
    {
        $this->selectedBookingId = $bookingId;
        $this->transactionReference = '';
        $this->paymentMethod = 'edc';
        $this->paymentSuccess = null;
        $this->errorMessage = null;
    }

    public function closePaymentModal()
    {
        $this->selectedBookingId = null;
    }

    public function processPayment()
    {
        if (!$this->selectedBookingId) return;

        try {
            $booking = Booking::with('payments')->findOrFail($this->selectedBookingId);
            
            // Execute completion first
            $completeAction = new CompleteBooking();
            $completeAction->execute($booking, auth()->id());

            // Handle payment records
            if ($booking->payments->isEmpty()) {
                Payment::create([
                    'booking_id' => $booking->id,
                    'payment_method' => $this->paymentMethod,
                    'transaction_reference' => $this->transactionReference ?: 'EDC-' . strtoupper(Str::random(6)),
                    'amount' => $booking->net_amount,
                    'status' => 'paid',
                    'paid_at' => now()
                ]);
            } else {
                foreach ($booking->payments as $payment) {
                    $payment->update([
                        'status' => 'paid',
                        'payment_method' => $this->paymentMethod,
                        'transaction_reference' => $this->transactionReference ?: 'EDC-' . strtoupper(Str::random(6)),
                        'paid_at' => now()
                    ]);
                }
            }

            $this->paymentSuccess = "Booking {$booking->booking_code} berhasil dilunasi via " . strtoupper($this->paymentMethod) . "!";
            $this->selectedBookingId = null;
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render()
    {
        if (!Auth::check() || !in_array(Auth::user()->role, ['outlet_admin', 'super_admin'])) {
            return view('livewire.tablet.styscreen-login')
                ->layout('layouts.tablet-blank');
        }

        $outletId = auth()->user()->outlet_id ?? session('tablet_outlet_id', 1);
        $outlet = Outlet::find($outletId);
        $today = Carbon::today()->toDateString();

        // Load today's bookings for this outlet
        $query = Booking::where('outlet_id', $outletId)
            ->whereDate('booking_date', $today)
            ->with(['customer', 'stylist', 'items.service', 'payments']);

        if ($this->searchQuery) {
            $q = $this->searchQuery;
            $query->where(function($sub) use ($q) {
                $sub->where('booking_code', 'like', "%{$q}%")
                    ->orWhereHas('customer', function($c) use ($q) {
                        $c->where('name', 'like', "%{$q}%")
                          ->orWhere('phone', 'like', "%{$q}%");
                    });
            });
        }

        $bookings = $query->latest()->get();

        // Categorize bookings for Styscreen lanes
        $unpaidBookings = $bookings->filter(function($b) {
            $isPaid = $b->payments->where('status', 'paid')->isNotEmpty();
            return !$isPaid && $b->status !== 'cancelled' && $b->status !== 'expired';
        });

        $paidActiveBookings = $bookings->filter(function($b) {
            $isPaid = $b->payments->where('status', 'paid')->isNotEmpty();
            return $isPaid && in_array($b->status, ['confirmed', 'checked_in', 'in_progress']);
        });

        $completedBookings = $bookings->filter(function($b) {
            return $b->status === 'completed';
        });

        // Load booking details for print preview if modal is open
        $selectedBooking = $this->selectedBookingId ? Booking::with(['customer', 'stylist', 'items.service', 'payments'])->find($this->selectedBookingId) : null;

        return view('livewire.tablet.styscreen', compact(
            'unpaidBookings', 'paidActiveBookings', 'completedBookings', 'outlet', 'selectedBooking'
        ))->layout('layouts.tablet');
    }
}
