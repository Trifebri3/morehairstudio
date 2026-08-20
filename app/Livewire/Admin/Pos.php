<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Domains\Customer\Models\Customer;
use App\Domains\Service\Models\Service;
use App\Domains\POS\Models\Product;
use App\Domains\POS\Models\PosTransaction;
use App\Domains\POS\Services\POSTransactionService;
use App\Domains\Booking\Models\Booking;
use App\Domains\Stylist\Models\Stylist;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class Pos extends Component
{
    // Scopes
    public $selectedOutletId = '';

    // Cart state
    public $cart = [];
    public $cartDiscount = 0;
    public $notes = '';

    // Customer & Booking linkages
    public $selectedCustomerId = '';
    public $selectedBookingId = '';
    public $selectedStaffId = '';

    // Checkout configurations
    public $paymentMethod = 'cash';
    public $transactionReference = '';
    public $bookingPaidOnline = false;

    // Modals & confirmation
    public $showSuccessReceipt = false;
    public $lastTxId = null;

    // Search query parameters
    public $searchProduct = '';
    public $searchService = '';

    protected $listeners = ['refreshPos' => '$refresh'];

    public function mount()
    {
        Gate::authorize('pos.view');

        // Scope to outlet if outlet_admin
        if (auth()->user()->role === 'outlet_admin') {
            $this->selectedOutletId = auth()->user()->outlet_id;
        } else {
            // Default to first outlet
            $firstOutlet = \App\Domains\Outlet\Models\Outlet::first();
            $this->selectedOutletId = $firstOutlet ? $firstOutlet->id : '';
        }
    }

    public function selectBooking($bookingId)
    {
        $booking = Booking::with(['items.service', 'customer', 'payments'])->findOrFail($bookingId);
        
        $this->selectedBookingId = $booking->id;
        $this->selectedCustomerId = $booking->customer_id;
        $this->selectedStaffId = $booking->stylist_id;

        // Clear existing cart and load booking items
        $this->cart = [];
        foreach ($booking->items as $item) {
            $this->addToCart('service', $item->service_id, $item->service->name, $item->price);
        }

        // Apply discount if promotion applied
        if ($booking->discount_amount > 0) {
            $this->cartDiscount = $booking->discount_amount;
        }

        // Check if already paid online
        $totalPaid = $booking->payments->where('status', 'completed')->sum('amount');
        $this->bookingPaidOnline = $totalPaid >= $booking->net_amount;

        if ($this->bookingPaidOnline) {
            $this->paymentMethod = 'gateway';
            $this->transactionReference = 'PAID-ONLINE-GATEWAY';
        } else {
            $this->paymentMethod = 'cash';
            $this->transactionReference = '';
        }
    }

    public function addToCart($type, $id, $name = null, $price = null)
    {
        // Check if already in cart, increment quantity
        foreach ($this->cart as $index => $item) {
            if ($item['type'] === $type && $item['id'] == $id) {
                $this->cart[$index]['qty']++;
                return;
            }
        }

        if ($type === 'service') {
            $service = Service::findOrFail($id);
            $name = $service->name;
            $price = $service->default_price;
        } else {
            $product = Product::findOrFail($id);
            $name = $product->name;
            $price = $product->selling_price;
        }

        $this->cart[] = [
            'type' => $type,
            'id' => $id,
            'name' => $name,
            'price' => $price,
            'qty' => 1,
            'discount' => 0
        ];
    }

    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function updateQty($index, $qty)
    {
        if ($qty < 1) $qty = 1;
        $this->cart[$index]['qty'] = intval($qty);
    }

    public function updateDiscount($index, $discount)
    {
        if ($discount < 0) $discount = 0;
        $this->cart[$index]['discount'] = floatval($discount);
    }

    public function checkout()
    {
        Gate::authorize('pos.create');

        $this->validate([
            'selectedOutletId' => 'required',
            'paymentMethod' => 'required|in:cash,qris,transfer,ewallet,gateway',
            'cart' => 'required|array|min:1',
            'cartDiscount' => 'nullable|numeric|min:0'
        ]);

        try {
            $transaction = POSTransactionService::checkout([
                'outlet_id' => $this->selectedOutletId,
                'customer_id' => $this->selectedCustomerId ?: null,
                'booking_id' => $this->selectedBookingId ?: null,
                'staff_id' => $this->selectedStaffId ?: null,
                'items' => $this->cart,
                'discount' => floatval($this->cartDiscount),
                'notes' => $this->notes,
                'payment_method' => $this->paymentMethod,
                'transaction_reference' => $this->transactionReference
            ]);

            $this->lastTxId = $transaction->id;
            $this->showSuccessReceipt = true;

            // Reset checkout state
            $this->cart = [];
            $this->cartDiscount = 0;
            $this->notes = '';
            $this->selectedCustomerId = '';
            $this->selectedBookingId = '';
            $this->selectedStaffId = '';
            $this->transactionReference = '';
            $this->bookingPaidOnline = false;

            session()->flash('message', 'Transaksi POS berhasil diselesaikan!');
        } catch (\Exception $e) {
            Log::error('POS Checkout error: ' . $e->getMessage());
            session()->flash('error', 'Checkout Gagal: ' . $e->getMessage());
        }
    }

    public function closeReceiptModal()
    {
        $this->showSuccessReceipt = false;
        $this->lastTxId = null;
    }

    public function render()
    {
        // 1. Fetch checked-in bookings for current outlet waiting for payment
        $pendingBookings = Booking::where('outlet_id', $this->selectedOutletId)
            ->whereIn('status', ['checked_in', 'pending'])
            ->with(['customer', 'items.service'])
            ->latest()
            ->get();

        // 2. Fetch products
        $productQuery = Product::where('is_active', true);
        if ($this->searchProduct) {
            $productQuery->where('name', 'like', '%' . $this->searchProduct . '%')
                         ->orWhere('sku', 'like', '%' . $this->searchProduct . '%');
        }
        $products = $productQuery->orderBy('name')->get();

        // 3. Fetch services
        $serviceQuery = Service::query();
        if ($this->searchService) {
            $serviceQuery->where('name', 'like', '%' . $this->searchService . '%');
        }
        $services = $serviceQuery->orderBy('name')->get();

        // 4. Fetch customers for checkout mapping
        $customers = Customer::orderBy('name')->get();

        // 5. Fetch stylists for staff mapping
        $stylists = Stylist::where('outlet_id', $this->selectedOutletId)->get();

        $outlets = \App\Domains\Outlet\Models\Outlet::all();

        // Calculate checkout metrics
        $subtotal = 0;
        foreach ($this->cart as $item) {
            $subtotal += ($item['price'] * $item['qty']) - $item['discount'];
        }
        $tax = round($subtotal * 0.10, 2);
        $grandTotal = max(0, $subtotal - floatval($this->cartDiscount) + $tax);

        $lastTransaction = $this->lastTxId ? PosTransaction::with(['items', 'customer', 'outlet', 'stylist'])->find($this->lastTxId) : null;

        return view('livewire.admin.pos', compact(
            'pendingBookings', 'products', 'services', 'customers', 'stylists', 'outlets',
            'subtotal', 'tax', 'grandTotal', 'lastTransaction'
        ))->layout('layouts.admin');
    }
}
