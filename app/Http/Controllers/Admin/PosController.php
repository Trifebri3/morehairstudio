<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\Customer\Models\Customer;
use App\Domains\Service\Models\Service;
use App\Domains\POS\Models\Product;
use App\Domains\POS\Models\PosTransaction;
use App\Domains\POS\Services\POSTransactionService;
use App\Domains\Booking\Models\Booking;
use App\Domains\Stylist\Models\Stylist;
use App\Domains\Outlet\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class PosController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('pos.view');

        // Scope to outlet if outlet_admin
        if (auth()->user()->role === 'outlet_admin') {
            $selectedOutletId = auth()->user()->outlet_id;
        } else {
            $firstOutlet = Outlet::first();
            $selectedOutletId = $request->get('outlet_id', $firstOutlet ? $firstOutlet->id : '');
        }

        // 1. Fetch checked-in bookings for current outlet waiting for payment
        $pendingBookings = Booking::where('outlet_id', $selectedOutletId)
            ->whereIn('status', ['checked_in', 'pending'])
            ->with(['customer', 'items.service'])
            ->latest()
            ->get();

        // 2. Fetch products
        $searchProduct = $request->get('search_product', '');
        $productQuery = Product::where('is_active', true);
        if ($searchProduct) {
            $productQuery->where(function($q) use ($searchProduct) {
                $q->where('name', 'like', '%' . $searchProduct . '%')
                  ->orWhere('sku', 'like', '%' . $searchProduct . '%');
            });
        }
        $products = $productQuery->orderBy('name')->get();

        // 3. Fetch services
        $searchService = $request->get('search_service', '');
        $serviceQuery = Service::query();
        if ($searchService) {
            $serviceQuery->where('name', 'like', '%' . $searchService . '%');
        }
        $services = $serviceQuery->orderBy('name')->get();

        // 4. Fetch customers for checkout mapping
        $customers = Customer::orderBy('name')->get();

        // 5. Fetch stylists for staff mapping
        $stylists = Stylist::where('outlet_id', $selectedOutletId)->get();

        $outlets = Outlet::all();

        // Check if there was a successful checkout
        $lastTransaction = null;
        if (session()->has('last_tx_id')) {
            $lastTransaction = PosTransaction::with(['items', 'customer', 'outlet', 'stylist'])
                ->find(session('last_tx_id'));
        }

        return view('admin.pos', compact(
            'selectedOutletId', 'pendingBookings', 'products', 'services',
            'customers', 'stylists', 'outlets', 'lastTransaction',
            'searchProduct', 'searchService'
        ));
    }

    /**
     * Get booking details as JSON for POS loading.
     */
    public function getBooking($id)
    {
        $booking = Booking::with(['items.service', 'customer', 'payments'])->findOrFail($id);
        
        $totalPaid = $booking->payments->where('status', 'completed')->sum('amount');
        $bookingPaidOnline = $totalPaid >= $booking->net_amount;

        $items = [];
        foreach ($booking->items as $item) {
            $items[] = [
                'type' => 'service',
                'id' => $item->service_id,
                'name' => $item->service->name,
                'price' => floatval($item->price),
                'qty' => 1,
                'discount' => 0
            ];
        }

        return response()->json([
            'id' => $booking->id,
            'customer_id' => $booking->customer_id,
            'stylist_id' => $booking->stylist_id,
            'discount_amount' => floatval($booking->discount_amount),
            'paid_online' => $bookingPaidOnline,
            'items' => $items
        ]);
    }

    /**
     * Process checkout.
     */
    public function checkout(Request $request)
    {
        Gate::authorize('pos.create');

        $request->validate([
            'outlet_id' => 'required',
            'payment_method' => 'required|in:cash,qris,transfer,ewallet,gateway',
            'cart_json' => 'required|string',
            'discount' => 'nullable|numeric|min:0'
        ]);

        try {
            $cart = json_decode($request->cart_json, true);
            if (empty($cart)) {
                return back()->with('error', 'Keranjang belanja kosong.');
            }

            // Normalise items for POS service
            $items = [];
            foreach ($cart as $item) {
                $items[] = [
                    'type' => $item['type'],
                    'id' => $item['id'],
                    'price' => floatval($item['price']),
                    'qty' => intval($item['qty']),
                    'discount' => floatval($item['discount'] ?? 0)
                ];
            }

            $transaction = POSTransactionService::checkout([
                'outlet_id' => $request->outlet_id,
                'customer_id' => $request->customer_id ?: null,
                'booking_id' => $request->booking_id ?: null,
                'staff_id' => $request->staff_id ?: null,
                'items' => $items,
                'discount' => floatval($request->discount),
                'notes' => $request->notes,
                'payment_method' => $request->payment_method,
                'transaction_reference' => $request->transaction_reference
            ]);

            return redirect()->route('admin.pos', ['outlet_id' => $request->outlet_id])
                ->with('last_tx_id', $transaction->id)
                ->with('message', 'Transaksi POS berhasil diselesaikan!');
        } catch (\Exception $e) {
            Log::error('POS Checkout error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Checkout Gagal: ' . $e->getMessage());
        }
    }
}
