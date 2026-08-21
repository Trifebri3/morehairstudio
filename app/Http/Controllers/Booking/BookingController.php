<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Domains\Outlet\Models\Outlet;
use App\Domains\Service\Models\Service;
use App\Domains\Service\Models\ServiceCategory;
use App\Domains\Stylist\Models\Stylist;
use App\Domains\Customer\Models\Customer;
use App\Domains\Customer\Services\PhoneNormalizer;
use App\Domains\Promotion\Models\Promotion;
use App\Domains\Booking\Services\AvailabilityService;
use App\Domains\Booking\Actions\CreateBooking;
use App\Domains\Booking\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $walkIn = $request->has('walk_in');
        $preselectedOutletId = $request->get('outlet_id');

        $outlets = Outlet::where('status', 'active')->get();
        $categories = ServiceCategory::whereHas('services', function ($q) {
            $q->where('is_active', true);
        })->get();

        $services = Service::where('is_active', true)
            ->with(['category', 'outlets'])
            ->get()
            ->map(function ($s) {
                // Map outlet overrides
                $overrides = DB::table('outlet_services')
                    ->where('service_id', $s->id)
                    ->get()
                    ->keyBy('outlet_id');
                
                $s->outlet_overrides = $overrides;
                return $s;
            });

        $stylists = Stylist::where('status', 'active')->get();

        return view('booking.booking-wizard', compact(
            'outlets', 'categories', 'services', 'stylists', 'walkIn', 'preselectedOutletId'
        ));
    }

    public function getSlots(Request $request)
    {
        $request->validate([
            'outlet_id' => 'required|integer',
            'service_id' => 'required|integer',
            'date' => 'required|date',
            'walk_in' => 'nullable|boolean'
        ]);

        $outletId = $request->outlet_id;
        $serviceId = $request->service_id;
        $date = $request->date;
        $isWalkIn = filter_var($request->get('walk_in', false), FILTER_VALIDATE_BOOLEAN);

        $stylists = Stylist::where('outlet_id', $outletId)
            ->where('status', 'active')
            ->get();

        $availability = new AvailabilityService();
        $slots = [];

        foreach ($stylists as $stylist) {
            $slots[$stylist->id] = $availability->getAvailableSlots(
                $outletId,
                $stylist->id,
                $serviceId,
                $date,
                $isWalkIn
            );
        }

        return response()->json([
            'slots' => $slots,
            'stylists' => $stylists
        ]);
    }

    public function lookupCustomer(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:9'
        ]);

        $phone = $request->phone;
        $normalized = PhoneNormalizer::normalize($phone);

        $customer = Customer::where('phone', $normalized)
            ->orWhere('whatsapp_phone', $normalized)
            ->orWhere('phone', $phone)
            ->first();

        if ($customer) {
            return response()->json([
                'found' => true,
                'customer' => [
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'birth_date' => $customer->birth_date ? $customer->birth_date->toDateString() : '',
                    'gender' => $customer->gender
                ]
            ]);
        }

        return response()->json(['found' => false]);
    }

    public function applyPromo(Request $request)
    {
        $request->validate([
            'promo_code' => 'required|string',
            'service_price' => 'required|numeric'
        ]);

        $promoCode = trim($request->promo_code);
        $price = $request->service_price;

        $promo = Promotion::where('promo_code', $promoCode)
            ->where(function ($q) {
                $q->whereNull('start_at')->orWhere('start_at', '<=', Carbon::now());
            })
            ->where(function ($q) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', Carbon::now());
            })
            ->first();

        if (!$promo) {
            return response()->json(['success' => false, 'message' => 'Kode promo tidak valid atau telah kedaluwarsa.']);
        }

        if ($promo->usage_limit !== null && $promo->usage_count >= $promo->usage_limit) {
            return response()->json(['success' => false, 'message' => 'Limit penggunaan kode promo ini sudah habis.']);
        }

        if ($price < $promo->minimum_transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Minimum transaksi untuk promo ini adalah Rp ' . number_format($promo->minimum_transaction, 0, ',', '.')
            ]);
        }

        if ($promo->discount_type === 'percentage') {
            $discount = ($price * $promo->discount_value) / 100;
            if ($promo->maximum_discount !== null && $discount > $promo->maximum_discount) {
                $discount = $promo->maximum_discount;
            }
        } else {
            $discount = min($promo->discount_value, $price);
        }

        return response()->json([
            'success' => true,
            'discount' => $discount,
            'message' => 'Kode promo berhasil digunakan!'
        ]);
    }

    public function confirmBooking(Request $request)
    {
        $request->validate([
            'phone' => 'required|min:9',
            'customer_name' => 'required|string|min:3',
            'email' => 'nullable|email',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'outlet_id' => 'required|integer',
            'service_id' => 'required|integer',
            'stylist_id' => 'required|integer',
            'booking_date' => 'required|date',
            'booking_time' => 'required|string',
            'promo_code' => 'nullable|string',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
            'is_walk_in' => 'nullable|boolean'
        ]);

        try {
            $createBooking = new CreateBooking();
            
            $booking = $createBooking->execute([
                'phone' => $request->phone,
                'customer_name' => $request->customer_name,
                'email' => $request->email,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'outlet_id' => $request->outlet_id,
                'service_id' => $request->service_id,
                'stylist_id' => $request->stylist_id,
                'booking_date' => $request->booking_date,
                'booking_time' => $request->booking_time,
                'promo_code' => $request->promo_code,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'source' => $request->is_walk_in ? 'walk_in' : 'website'
            ]);

            $isGatewayActive = \App\Domains\CMS\Services\CmsService::get('payment_gateway_active') === 'true';

            if ($isGatewayActive && $request->payment_method === 'midtrans') {
                $params = [
                    'transaction_details' => [
                        'order_id' => $booking->booking_code . '-' . time(),
                        'gross_amount' => (int)$booking->net_amount,
                    ],
                    'customer_details' => [
                        'first_name' => $booking->customer->name,
                        'email' => $booking->customer->email,
                        'phone' => $booking->customer->phone,
                    ]
                ];

                $transaction = \App\Domains\Payment\Services\MidtransService::createTransaction($params);

                $payment = $booking->payments()->first();
                if ($payment) {
                    $payment->update([
                        'transaction_reference' => $transaction->redirect_url
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'redirect_url' => $transaction->redirect_url
                ]);
            }

            if ($request->is_walk_in) {
                return response()->json([
                    'success' => true,
                    'redirect_url' => route('tablet.check-in', ['status' => "Walk-In booking berhasil dibuat: {$booking->booking_code}"])
                ]);
            }

            return response()->json([
                'success' => true,
                'redirect_url' => route('booking.success', ['token' => $booking->booking_token])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function success($token)
    {
        $booking = Booking::where('booking_token', $token)
            ->with(['customer', 'outlet', 'stylist', 'items.service'])
            ->firstOrFail();

        return view('booking.success', compact('booking'));
    }
}
