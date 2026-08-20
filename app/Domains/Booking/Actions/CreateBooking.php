<?php

namespace App\Domains\Booking\Actions;

use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Models\BookingItem;
use App\Domains\Booking\Models\BookingStatusHistory;
use App\Domains\Booking\Exceptions\BookingUnavailableException;
use App\Domains\Booking\Exceptions\DoubleBookingException;
use App\Domains\Booking\Services\BookingCodeService;
use App\Domains\Customer\Models\Customer;
use App\Domains\Customer\Services\PhoneNormalizer;
use App\Domains\Service\Models\Service;
use App\Domains\Promotion\Models\Promotion;
use App\Domains\Payment\Models\Payment;
use App\Domains\WhatsApp\Models\WhatsAppMessage;
use App\Domains\Outlet\Models\Outlet;
use App\Domains\Stylist\Models\Stylist;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateBooking
{
    public function execute(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            // 1. Normalize phone number
            $phone = PhoneNormalizer::normalize($data['phone']);

            // 2. Find or create Customer record
            $customer = Customer::where('phone', $phone)->first();
            if (!$customer) {
                $count = Customer::count() + 1;
                $customerCode = 'CUST-' . str_pad((string)$count, 5, '0', STR_PAD_LEFT);
                $customer = Customer::create([
                    'customer_code' => $customerCode,
                    'phone' => $phone,
                    'whatsapp_phone' => $phone,
                    'name' => $data['customer_name'],
                    'email' => !empty($data['email']) ? $data['email'] : null,
                    'birth_date' => !empty($data['birth_date']) ? $data['birth_date'] : null,
                    'gender' => !empty($data['gender']) ? $data['gender'] : null,
                ]);
            } else {
                // Pre-fill / update missing values
                $updatedData = [];
                if (!$customer->email && !empty($data['email'])) {
                    $updatedData['email'] = $data['email'];
                }
                if (!$customer->birth_date && !empty($data['birth_date'])) {
                    $updatedData['birth_date'] = $data['birth_date'];
                }
                if (!$customer->gender && !empty($data['gender'])) {
                    $updatedData['gender'] = $data['gender'];
                }
                if (!empty($updatedData)) {
                    $customer->update($updatedData);
                }
            }

            $outletId = $data['outlet_id'];
            $stylistId = $data['stylist_id'];
            $serviceId = $data['service_id'];
            $dateString = $data['booking_date'];
            $timeString = $data['booking_time']; // Format H:i

            // 3. Resolve service pricing and duration
            $outletService = DB::table('outlet_services')
                ->where('outlet_id', $outletId)
                ->where('service_id', $serviceId)
                ->first();

            $price = $outletService ? ($outletService->price ?? null) : null;
            $duration = $outletService ? ($outletService->duration ?? null) : null;

            if ($price === null || $duration === null) {
                $service = Service::findOrFail($serviceId);
                $price = $price ?? $service->default_price;
                $duration = $duration ?? $service->default_duration;
            }

            $startTime = Carbon::createFromFormat('H:i', $timeString);
            $endTime = $startTime->copy()->addMinutes($duration);

            // 4. Overlap/double booking check
            $isOverlap = Booking::where('stylist_id', $stylistId)
                ->whereDate('booking_date', $dateString)
                ->whereNotIn('status', ['cancelled', 'expired'])
                ->whereHas('items', function ($q) use ($startTime, $endTime) {
                    $q->where(function ($sub) use ($startTime, $endTime) {
                        $sub->where('start_time', '<', $endTime->format('H:i:s'))
                            ->where('end_time', '>', $startTime->format('H:i:s'));
                    });
                })->exists();

            if ($isOverlap) {
                throw new DoubleBookingException();
            }

            // 5. Apply Coupon Code if any
            $discount = 0.00;
            if (!empty($data['promo_code'])) {
                $promo = Promotion::where('promo_code', $data['promo_code'])
                    ->where(function ($q) {
                        $q->whereNull('start_at')->orWhere('start_at', '<=', Carbon::now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('end_at')->orWhere('end_at', '>=', Carbon::now());
                    })
                    ->first();

                if ($promo) {
                    if ($promo->usage_limit === null || $promo->usage_count < $promo->usage_limit) {
                        if ($price >= $promo->minimum_transaction) {
                            if ($promo->discount_type === 'percentage') {
                                $discount = ($price * $promo->discount_value) / 100;
                                if ($promo->maximum_discount !== null && $discount > $promo->maximum_discount) {
                                    $discount = $promo->maximum_discount;
                                }
                            } else {
                                $discount = $promo->discount_value;
                            }
                            $promo->increment('usage_count');
                        }
                    }
                }
            }

            $netAmount = max(0.00, $price - $discount);

            // 6. Generate Codes and save
            $bookingCode = BookingCodeService::generateCode($dateString);
            $bookingToken = BookingCodeService::generateToken();

            $booking = Booking::create([
                'booking_code' => $bookingCode,
                'booking_token' => $bookingToken,
                'customer_id' => $customer->id,
                'outlet_id' => $outletId,
                'stylist_id' => $stylistId,
                'booking_date' => $dateString,
                'status' => 'pending',
                'source' => $data['source'] ?? 'website',
                'total_amount' => $price,
                'discount_amount' => $discount,
                'net_amount' => $netAmount,
                'promo_code' => $data['promo_code'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Save booking item
            BookingItem::create([
                'booking_id' => $booking->id,
                'service_id' => $serviceId,
                'price' => $price,
                'duration' => $duration,
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s'),
            ]);

            // Record history
            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'status' => 'pending',
                'reason' => 'Booking created successfully.'
            ]);

            // Create Payment
            Payment::create([
                'booking_id' => $booking->id,
                'payment_method' => $data['payment_method'] ?? 'manual',
                'amount' => $netAmount,
                'status' => ($data['payment_method'] ?? 'manual') === 'manual' ? 'pending' : 'paid', // manual means pay at outlet, so pending
                'paid_at' => ($data['payment_method'] ?? 'manual') === 'manual' ? null : Carbon::now()
            ]);

            // 7. Dispatch BookingCreated event
            event(new \App\Domains\Booking\Events\BookingCreated($booking));

            return $booking;
        });
    }
}
