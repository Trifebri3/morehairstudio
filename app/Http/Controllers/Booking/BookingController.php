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
        $walkIn = $request->has('walk_in') && $request->walk_in != '0' && $request->walk_in !== 'false';
        $preselectedOutletId = $request->get('outlet_id');
        $preselectedServiceId = $request->get('service_id');
        $stylistParam = $request->get('stylist_id') ?? $request->get('stylist');
        $preselectedStylistId = null;

        if ($stylistParam) {
            if (is_numeric($stylistParam)) {
                $stylistObj = Stylist::find($stylistParam);
            } else {
                $clean = strtolower(trim($stylistParam));
                $stylistObj = Stylist::where('status', 'active')
                    ->where(function($q) use ($clean) {
                        $q->where('slug', $clean)
                          ->orWhereRaw('LOWER(name) = ?', [$clean])
                          ->orWhereRaw("LOWER(REPLACE(name, ' ', '-')) = ?", [$clean])
                          ->orWhereRaw("LOWER(REPLACE(name, ' ', '')) = ?", [$clean]);
                    })
                    ->first();
            }

            if ($stylistObj) {
                $preselectedStylistId = $stylistObj->id;
                if (!$preselectedOutletId) {
                    $preselectedOutletId = $stylistObj->outlet_id;
                }
            }
        }

        $outlets = Outlet::where('status', 'active')->get();
        $categories = ServiceCategory::whereHas('services', function ($q) {
            $q->where('is_active', true);
        })->get();

        // Fetch all outlet overrides in a single bulk query to eliminate loop DB calls
        $allOverrides = DB::table('outlet_services')
            ->get()
            ->groupBy('service_id');

        $services = Service::where('is_active', true)
            ->with(['category', 'outlets'])
            ->get()
            ->map(function ($s) use ($allOverrides) {
                $s->outlet_overrides = $allOverrides->get($s->id, collect())->keyBy('outlet_id');
                return $s;
            });

        $stylists = Stylist::where('status', 'active')->get();

        return view('booking.booking-wizard', compact(
            'outlets', 'categories', 'services', 'stylists', 'walkIn', 
            'preselectedOutletId', 'preselectedServiceId', 'preselectedStylistId'
        ));
    }

    public function getSlots(Request $request)
    {
        $request->validate([
            'outlet_id' => 'required|integer',
            'service_id' => 'nullable|integer',
            'date' => 'required|date',
            'walk_in' => 'nullable|boolean'
        ]);

        $outletId = (int)$request->outlet_id;
        $serviceId = $request->service_id ? (int)$request->service_id : null;
        if (!$serviceId) {
            $outlet = Outlet::find($outletId);
            $firstService = $outlet ? $outlet->services()->wherePivot('is_active', true)->first() : null;
            $serviceId = $firstService ? $firstService->id : 1;
        }

        $date = $request->date;
        $isWalkIn = filter_var($request->get('walk_in', false), FILTER_VALIDATE_BOOLEAN);

        $stylists = Stylist::where('outlet_id', $outletId)
            ->where('status', 'active')
            ->get();

        $availability = new AvailabilityService();
        $slots = [];
        $busyIntervals = [];
        $workingHours = [];
        $freeWindows = [];

        // Auto-expire any past no-show bookings for this outlet so expired slots are immediately freed
        Booking::autoExpireNoShows($outletId);

        $carbonDate = Carbon::parse($date);
        $dayOfWeek = $carbonDate->dayOfWeek;
        $outlet = Outlet::find($outletId);
        $leadTimeHours = $outlet ? (int)$outlet->booking_lead_time_hours : 1;

        $dayName = strtolower($carbonDate->format('l'));
        $outletHours = ($outlet && isset($outlet->opening_hours[$dayName])) ? $outlet->opening_hours[$dayName] : null;
        $defaultOpen = ($outletHours && isset($outletHours['open'])) ? $outletHours['open'] : '10:00';
        $defaultClose = ($outletHours && isset($outletHours['close'])) ? $outletHours['close'] : '20:00';
        $isOutletOpen = $outletHours ? (bool)($outletHours['is_open'] ?? true) : true;

        foreach ($stylists as $stylist) {
            $slots[$stylist->id] = $availability->getAvailableSlots(
                $outletId,
                $stylist->id,
                $serviceId,
                $date,
                $isWalkIn
            );

            $schedule = DB::table('stylist_schedules')
                ->where('stylist_id', $stylist->id)
                ->where('day_of_week', $dayOfWeek)
                ->first();

            $isWorking = $schedule ? (bool)$schedule->is_working : $isOutletOpen;
            $startTime = ($schedule && $schedule->start_time) ? substr($schedule->start_time, 0, 5) : $defaultOpen;
            $endTime = ($schedule && $schedule->end_time) ? substr($schedule->end_time, 0, 5) : $defaultClose;

            $workingHours[$stylist->id] = [
                'is_working' => $isWorking,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ];

            // Existing bookings for this stylist
            $stylistBookings = Booking::where('stylist_id', $stylist->id)
                ->whereDate('booking_date', $date)
                ->whereNotIn('status', ['cancelled', 'expired'])
                ->with(['items.service'])
                ->get();

            $intervals = [];
            foreach ($stylistBookings as $b) {
                foreach ($b->items as $item) {
                    $serviceName = $item->service ? $item->service->name : 'Haircut & Grooming';
                    $intervals[] = [
                        'start' => substr($item->start_time, 0, 5),
                        'end' => substr($item->end_time, 0, 5),
                        'type' => 'booking',
                        'service_name' => $serviceName,
                        'label' => 'Terisi'
                    ];
                }
            }

            // Sort intervals chronologically
            usort($intervals, function ($a, $b) {
                return strcmp($a['start'], $b['start']);
            });

            $busyIntervals[$stylist->id] = $intervals;

            // Check real-time treatment right now
            $nowTime = Carbon::now()->format('H:i:s');
            $activeBooking = Booking::where('stylist_id', $stylist->id)
                ->whereDate('booking_date', Carbon::today())
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->whereHas('items', function($q) use ($nowTime) {
                    $q->where('start_time', '<=', $nowTime)
                      ->where('end_time', '>=', $nowTime);
                })
                ->with(['items.service', 'customer'])
                ->first();

            $liveStatus[$stylist->id] = [
                'is_busy' => $activeBooking !== null,
                'service_name' => $activeBooking?->items->first()?->service?->name ?? null,
                'finish_time' => $activeBooking ? substr($activeBooking->items->first()?->end_time ?? '', 0, 5) : null,
                'status_label' => $activeBooking ? 'Sedang Melayani Customer' : 'Kursi Siap • Tersedia Sekarang',
            ];

            // Calculate free windows if stylist is working
            if ($isWorking) {
                $freeWindows[$stylist->id] = $this->calculateFreeWindows($startTime, $endTime, $intervals);
            } else {
                $freeWindows[$stylist->id] = [];
            }
        }

        return response()->json([
            'slots' => $slots,
            'stylists' => $stylists,
            'busy_intervals' => $busyIntervals,
            'free_windows' => $freeWindows,
            'working_hours' => $workingHours,
            'live_status' => $liveStatus,
            'lead_time_hours' => $leadTimeHours,
            'server_time' => Carbon::now()->format('H:i'),
            'today_date' => Carbon::today()->toDateString()
        ]);
    }

    private function calculateFreeWindows(string $dayStart, string $dayEnd, array $busyIntervals): array
    {
        $windows = [];
        $current = $dayStart;

        foreach ($busyIntervals as $interval) {
            $intStart = $interval['start'];
            $intEnd = $interval['end'];

            if ($intStart > $current) {
                $windows[] = [
                    'start' => $current,
                    'end' => $intStart,
                ];
            }
            if ($intEnd > $current) {
                $current = $intEnd;
            }
        }

        if ($current < $dayEnd) {
            $windows[] = [
                'start' => $current,
                'end' => $dayEnd,
            ];
        }

        return $windows;
    }

    public function getMonthOccupancy(Request $request)
    {
        $request->validate([
            'outlet_id' => 'required|integer',
            'year' => 'required|integer',
            'month' => 'required|integer|between:1,12',
        ]);

        $outletId = (int)$request->outlet_id;
        $year = (int)$request->year;
        $month = (int)$request->month;

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $bookings = Booking::where('outlet_id', $outletId)
            ->whereDate('booking_date', '>=', $startDate->toDateString())
            ->whereDate('booking_date', '<=', $endDate->toDateString())
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->select('booking_date', DB::raw('count(*) as count'))
            ->groupBy('booking_date')
            ->pluck('count', 'booking_date')
            ->toArray();

        return response()->json([
            'occupancy' => $bookings,
            'year' => $year,
            'month' => $month,
        ]);
    }

    public function checkInterval(Request $request)
    {
        $request->validate([
            'outlet_id' => 'required|integer',
            'stylist_id' => 'required|integer',
            'service_id' => 'required|integer',
            'date' => 'required|date',
            'time' => 'required|string',
            'walk_in' => 'nullable|boolean'
        ]);

        $availability = new AvailabilityService();
        $res = $availability->isIntervalAvailable(
            (int)$request->outlet_id,
            (int)$request->stylist_id,
            (int)$request->service_id,
            $request->date,
            $request->time,
            filter_var($request->get('walk_in', false), FILTER_VALIDATE_BOOLEAN)
        );

        return response()->json($res);
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
            'booking_date' => 'required_without:is_walk_in|nullable|date',
            'booking_time' => 'required_without:is_walk_in|nullable|string',
            'promo_code' => 'nullable|string',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
            'is_walk_in' => 'nullable|boolean'
        ]);

        try {
            $isWalkIn = filter_var($request->is_walk_in, FILTER_VALIDATE_BOOLEAN);
            $bookingDate = $isWalkIn ? Carbon::today()->toDateString() : $request->booking_date;
            $bookingTime = $isWalkIn ? ($request->booking_time ?: Carbon::now()->format('H:i')) : $request->booking_time;

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
                'booking_date' => $bookingDate,
                'booking_time' => $bookingTime,
                'promo_code' => $request->promo_code,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'source' => $isWalkIn ? 'walk_in' : 'website'
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
