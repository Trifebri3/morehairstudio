<?php

namespace App\Livewire\Booking;

use Livewire\Component;
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
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingWizard extends Component
{
    // Wizard state (Consolidated 4 Steps)
    public $currentStep = 1;
    public $isWalkIn = false;
    
    // Selection state
    public $selectedOutletId = null;
    public $selectedServiceId = null;
    public $selectedStylistId = null;
    public $selectedDate = null;
    public $selectedTime = null;
    
    // Customer state
    public $phone = '';
    public $customerName = '';
    public $email = '';
    public $birthDate = '';
    public $gender = '';
    public $notes = '';
    
    // Promo state
    public $promoCode = '';
    public $discountAmount = 0.00;
    public $promoError = null;
    public $promoSuccess = null;
    
    // Payment state
    public $paymentMethod = 'manual';

    // Pricing details
    public $servicePrice = 0.00;
    public $serviceDuration = 0;

    // GPS status
    public $gpsLat = null;
    public $gpsLng = null;
    public $gpsStatus = '';

    protected $listeners = [
        'setGpsLocation' => 'applyGpsLocation',
        'restoreDraft' => 'restoreDraft'
    ];

    public function mount()
    {
        $this->selectedDate = Carbon::now()->toDateString();
    }

    public function selectOutlet($id)
    {
        $this->selectedOutletId = $id;
        $this->selectedServiceId = null;
        $this->selectedStylistId = null;
        $this->selectedTime = null;
    }

    public function selectService($id)
    {
        $this->selectedServiceId = $id;
        
        $outletService = DB::table('outlet_services')
            ->where('outlet_id', $this->selectedOutletId)
            ->where('service_id', $id)
            ->first();

        if ($outletService) {
            $this->servicePrice = $outletService->price ?? Service::find($id)->default_price;
            $this->serviceDuration = $outletService->duration ?? Service::find($id)->default_duration;
        } else {
            $service = Service::findOrFail($id);
            $this->servicePrice = $service->default_price;
            $this->serviceDuration = $service->default_duration;
        }

        // Advance to Step 2: Choose Barber
        $this->currentStep = 2;
        $this->selectedStylistId = null;
        $this->selectedTime = null;
    }

    public function selectStylist($id)
    {
        $this->selectedStylistId = $id;
        // Advance to Step 3: Choose Date & Time
        $this->currentStep = 3;
        $this->selectedTime = null;
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->selectedTime = null;
    }

    public function selectTime($time)
    {
        $this->selectedTime = $time;
        // Advance to Step 4: Confirm
        $this->currentStep = 4;
    }

    public function selectStylistAndSlot($stylistId, $time)
    {
        $this->selectedStylistId = $stylistId;
        $this->selectedTime = $time;
        $this->currentStep = 4;
    }

    /**
     * Auto-Fill Lookup on phone changes
     */
    public function updatedPhone()
    {
        if (strlen($this->phone) >= 9) {
            $normalized = PhoneNormalizer::normalize($this->phone);
            $customer = Customer::where('phone', $normalized)
                ->orWhere('whatsapp_phone', $normalized)
                ->orWhere('phone', $this->phone)
                ->first();

            if ($customer) {
                $this->customerName = $customer->name;
                $this->email = $customer->email;
                $this->birthDate = $customer->birth_date ? $customer->birth_date->toDateString() : '';
                $this->gender = $customer->gender;
                session()->flash('autoFillSuccess', 'Data customer ditemukan! Data terisi otomatis.');
            }
        }
    }

    /**
     * Restore booking state from client draft
     */
    public function restoreDraft($draft)
    {
        if (empty($draft)) return;

        if (isset($draft['selectedOutletId'])) {
            $this->selectedOutletId = $draft['selectedOutletId'];
        }
        if (isset($draft['selectedServiceId'])) {
            $this->selectedServiceId = $draft['selectedServiceId'];
            
            // Reload service pricing and duration
            $outletService = DB::table('outlet_services')
                ->where('outlet_id', $this->selectedOutletId)
                ->where('service_id', $this->selectedServiceId)
                ->first();

            if ($outletService) {
                $this->servicePrice = $outletService->price ?? Service::find($this->selectedServiceId)->default_price;
                $this->serviceDuration = $outletService->duration ?? Service::find($this->selectedServiceId)->default_duration;
            } else {
                $service = Service::find($this->selectedServiceId);
                if ($service) {
                    $this->servicePrice = $service->default_price;
                    $this->serviceDuration = $service->default_duration;
                }
            }
        }
        if (isset($draft['selectedStylistId'])) {
            $this->selectedStylistId = $draft['selectedStylistId'];
        }
        if (isset($draft['selectedDate'])) {
            $this->selectedDate = $draft['selectedDate'];
        }
        if (isset($draft['selectedTime'])) {
            $this->selectedTime = $draft['selectedTime'];
        }
        if (isset($draft['phone'])) {
            $this->phone = $draft['phone'];
        }
        if (isset($draft['customerName'])) {
            $this->customerName = $draft['customerName'];
        }
        if (isset($draft['email'])) {
            $this->email = $draft['email'];
        }
        if (isset($draft['birthDate'])) {
            $this->birthDate = $draft['birthDate'];
        }
        if (isset($draft['gender'])) {
            $this->gender = $draft['gender'];
        }
        if (isset($draft['notes'])) {
            $this->notes = $draft['notes'];
        }
        if (isset($draft['promoCode'])) {
            $this->promoCode = $draft['promoCode'];
            $this->updatedPromoCode();
        }
        if (isset($draft['currentStep'])) {
            $this->currentStep = $draft['currentStep'];
        }
    }

    /**
     * Auto-apply promo code when updated in real-time
     */
    public function updatedPromoCode()
    {
        $this->promoError = null;
        $this->promoSuccess = null;
        $this->discountAmount = 0.00;

        if (empty($this->promoCode)) {
            return;
        }

        $promo = Promotion::where('promo_code', trim($this->promoCode))
            ->where(function ($q) {
                $q->whereNull('start_at')->orWhere('start_at', '<=', Carbon::now());
            })
            ->where(function ($q) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', Carbon::now());
            })
            ->first();

        if ($promo) {
            if ($promo->usage_limit === null || $promo->usage_count < $promo->usage_limit) {
                if ($this->servicePrice >= $promo->minimum_transaction) {
                    if ($promo->discount_type === 'percentage') {
                        $discount = ($this->servicePrice * $promo->discount_value) / 100;
                        if ($promo->maximum_discount !== null && $discount > $promo->maximum_discount) {
                            $discount = $promo->maximum_discount;
                        }
                        $this->discountAmount = $discount;
                    } else {
                        $this->discountAmount = min($promo->discount_value, $this->servicePrice);
                    }
                    $this->promoSuccess = 'Kode promo berhasil digunakan!';
                }
            }
        }
    }

    public function applyPromo()
    {
        $this->promoError = null;
        $this->promoSuccess = null;
        $this->discountAmount = 0.00;

        if (empty($this->promoCode)) {
            return;
        }

        $promo = Promotion::where('promo_code', trim($this->promoCode))
            ->where(function ($q) {
                $q->whereNull('start_at')->orWhere('start_at', '<=', Carbon::now());
            })
            ->where(function ($q) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', Carbon::now());
            })
            ->first();

        if (!$promo) {
            $this->promoError = 'Kode promo tidak valid atau telah kedaluwarsa.';
            return;
        }

        if ($promo->usage_limit !== null && $promo->usage_count >= $promo->usage_limit) {
            $this->promoError = 'Limit penggunaan kode promo ini sudah habis.';
            return;
        }

        if ($this->servicePrice < $promo->minimum_transaction) {
            $this->promoError = 'Minimum transaksi untuk promo ini adalah Rp ' . number_format($promo->minimum_transaction, 0, ',', '.');
            return;
        }

        if ($promo->discount_type === 'percentage') {
            $discount = ($this->servicePrice * $promo->discount_value) / 100;
            if ($promo->maximum_discount !== null && $discount > $promo->maximum_discount) {
                $discount = $promo->maximum_discount;
            }
            $this->discountAmount = $discount;
        } else {
            $this->discountAmount = min($promo->discount_value, $this->servicePrice);
        }

        $this->promoSuccess = 'Kode promo berhasil digunakan!';
    }

    public function selectPayment($method)
    {
        $this->paymentMethod = $method;
    }

    public function confirmBooking()
    {
        $this->validate([
            'phone' => 'required|min:9',
            'customerName' => 'required|string|min:3',
            'email' => 'nullable|email',
            'birthDate' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
        ]);

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
            'source' => 'website'
        ]);

        $this->dispatch('clear-draft');

        $isGatewayActive = \App\Domains\CMS\Services\CmsService::get('payment_gateway_active') === 'true';

        if ($isGatewayActive && $this->paymentMethod === 'midtrans') {
            try {
                $params = [
                    'transaction_details' => [
                        'order_id' => $booking->booking_code . '-' . time(),
                        'gross_amount' => (int) $booking->net_amount,
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

                return redirect()->away($transaction->redirect_url);
            } catch (\Exception $e) {
                logger()->error('Midtrans Snap generation failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('booking.success', ['token' => $booking->booking_token]);
    }

    /**
     * Browser GPS Detection
     */
    public function applyGpsLocation($lat, $lng)
    {
        $this->gpsLat = $lat;
        $this->gpsLng = $lng;

        $locator = new \App\Domains\Outlet\Services\OutletLocatorService();
        $nearest = $locator->findNearest($lat, $lng, 1);

        if (!empty($nearest)) {
            $this->selectedOutletId = $nearest[0]['outlet']->id;
            $this->gpsStatus = 'Nearest outlet detected: ' . $nearest[0]['outlet']->name;
            $this->selectedServiceId = null;
            $this->selectedStylistId = null;
            $this->selectedTime = null;
        } else {
            $this->gpsStatus = 'Could not compute closest outlet.';
        }
    }

    public static function success($token)
    {
        $booking = Booking::where('booking_token', $token)->with(['customer', 'outlet', 'stylist', 'items.service'])->firstOrFail();
        return view('booking.success', compact('booking'))->layout('layouts.booking');
    }

    public function prevStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function render()
    {
        $outlets = Outlet::where('status', 'active')->get();
        
        $categories = [];
        $services = [];
        if ($this->selectedOutletId) {
            $services = Service::where('is_active', true)
                ->whereHas('outlets', function ($q) {
                    $q->where('outlet_id', $this->selectedOutletId)->where('is_active', true);
                })
                ->with('category')
                ->get();
                
            $categories = ServiceCategory::whereHas('services', function ($q) {
                $q->where('is_active', true)->whereHas('outlets', function ($o) {
                    $o->where('outlet_id', $this->selectedOutletId)->where('is_active', true);
                });
            })->get();
        }

        $stylists = [];
        if ($this->selectedOutletId) {
            $stylists = Stylist::where('outlet_id', $this->selectedOutletId)
                ->where('status', 'active')
                ->get();
        }

        $slots = [];
        $stylistSlots = [];
        $alternativeStylist = null;
        if ($this->selectedOutletId && $this->selectedServiceId && $this->selectedDate) {
            $availability = new AvailabilityService();
            
            // Generate slots for each active stylist
            foreach ($stylists as $stylistItem) {
                $stylistSlots[$stylistItem->id] = $availability->getAvailableSlots(
                    $this->selectedOutletId,
                    $stylistItem->id,
                    $this->selectedServiceId,
                    $this->selectedDate,
                    $this->isWalkIn
                );
            }

            // Reference selected stylist slots
            if ($this->selectedStylistId) {
                $slots = $stylistSlots[$this->selectedStylistId] ?? [];
                
                if (empty($slots)) {
                    foreach ($stylistSlots as $sId => $sSlots) {
                        if ($sId != $this->selectedStylistId && !empty($sSlots)) {
                            $alternativeStylist = $stylists->firstWhere('id', $sId);
                            break;
                        }
                    }
                }
            }
        }

        $selectedOutlet = $this->selectedOutletId ? Outlet::find($this->selectedOutletId) : null;
        $selectedService = $this->selectedServiceId ? Service::find($this->selectedServiceId) : null;
        $selectedStylist = $this->selectedStylistId ? Stylist::find($this->selectedStylistId) : null;

        return view('livewire.booking.booking-wizard', compact(
            'outlets', 'categories', 'services', 'stylists', 'slots', 'stylistSlots',
            'selectedOutlet', 'selectedService', 'selectedStylist', 'alternativeStylist'
        ))->layout('layouts.booking');
    }
}
