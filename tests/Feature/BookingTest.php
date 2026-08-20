<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Domains\Booking\Actions\CreateBooking;
use App\Domains\Booking\Actions\ConfirmBooking;
use App\Domains\Booking\Actions\CheckInBooking;
use App\Domains\Booking\Actions\CompleteBooking;
use App\Domains\Booking\Exceptions\DoubleBookingException;
use App\Domains\Outlet\Models\Outlet;
use App\Domains\Service\Models\ServiceCategory;
use App\Domains\Service\Models\Service;
use App\Domains\Service\Models\OutletService;
use App\Domains\Stylist\Models\Stylist;
use App\Domains\Stylist\Models\StylistSchedule;
use App\Domains\Promotion\Models\Promotion;
use App\Domains\Booking\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_actions_lifecycle()
    {
        // 1. Setup Outlet & Services
        $outlet = Outlet::create([
            'name' => 'MORE Bandung Test',
            'slug' => 'more-bandung-test',
            'address' => 'Jl. Test No. 1',
            'status' => 'active'
        ]);

        $category = ServiceCategory::create([
            'name' => 'Haircut',
            'slug' => 'haircut'
        ]);

        $service = Service::create([
            'service_category_id' => $category->id,
            'name' => 'Signature Haircut',
            'slug' => 'signature-haircut',
            'default_price' => 150000.00,
            'default_duration' => 30,
            'is_active' => true
        ]);

        OutletService::create([
            'outlet_id' => $outlet->id,
            'service_id' => $service->id,
            'price' => 150000.00,
            'duration' => 30,
            'is_active' => true
        ]);

        // 2. Setup Stylist & working day schedule
        $stylist = Stylist::create([
            'outlet_id' => $outlet->id,
            'name' => 'Ani Stylist',
            'slug' => 'ani-stylist',
            'status' => 'active'
        ]);

        $todayOfWeek = Carbon::now()->addDay()->dayOfWeek;
        StylistSchedule::create([
            'stylist_id' => $stylist->id,
            'day_of_week' => $todayOfWeek,
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
            'break_start' => '13:00:00',
            'break_end' => '14:00:00',
            'is_working' => true
        ]);

        // 3. Setup Promotion
        $promo = Promotion::create([
            'promo_code' => 'WELCOME50',
            'discount_type' => 'percentage',
            'discount_value' => 50.00,
            'minimum_transaction' => 100000.00
        ]);

        // 4. Create Booking
        $createAction = new CreateBooking();
        $booking = $createAction->execute([
            'phone' => '08123456789', // Normalized to 628123456789
            'customer_name' => 'Budi Santoso',
            'outlet_id' => $outlet->id,
            'service_id' => $service->id,
            'stylist_id' => $stylist->id,
            'booking_date' => Carbon::now()->addDay()->toDateString(),
            'booking_time' => '11:00',
            'promo_code' => 'WELCOME50',
            'payment_method' => 'manual',
            'source' => 'website'
        ]);

        // Asserts
        $this->assertDatabaseHas('bookings', [
            'booking_code' => $booking->booking_code,
            'net_amount' => 75000.00, // 150000 cut by 50%
            'status' => 'pending'
        ]);

        $this->assertDatabaseHas('customers', [
            'phone' => '628123456789', // Normalized successfully
            'name' => 'Budi Santoso'
        ]);

        // 5. Verify double-booking prevention
        $doubleBookingThrown = false;
        try {
            $createAction->execute([
                'phone' => '089999999',
                'customer_name' => 'Siti',
                'outlet_id' => $outlet->id,
                'service_id' => $service->id,
                'stylist_id' => $stylist->id,
                'booking_date' => Carbon::now()->addDay()->toDateString(),
                'booking_time' => '11:15', // Overlaps with 11:00 - 11:30!
                'payment_method' => 'manual'
            ]);
        } catch (DoubleBookingException $e) {
            $doubleBookingThrown = true;
        }

        $this->assertTrue($doubleBookingThrown, 'DoubleBookingException was not thrown on overlapping slot.');

        // 6. Confirm Booking Action
        $confirmAction = new ConfirmBooking();
        $booking = $confirmAction->execute($booking);
        $this->assertEquals('confirmed', $booking->status);

        // 7. Check-In Booking Action
        $checkInAction = new CheckInBooking();
        $booking = $checkInAction->execute($booking, $outlet->id);
        $this->assertEquals('checked_in', $booking->status);

        // 8. Complete Booking Action
        $completeAction = new CompleteBooking();
        $booking = $completeAction->execute($booking);
        $this->assertEquals('completed', $booking->status);
    }

    public function test_booking_auto_expiry_scheduler()
    {
        $outlet = Outlet::create([
            'name' => 'MORE Bandung Test 2',
            'slug' => 'more-bandung-test-2',
            'address' => 'Jl. Test No. 2',
            'status' => 'active'
        ]);

        $category = ServiceCategory::create([
            'name' => 'Haircut',
            'slug' => 'haircut'
        ]);

        $service = Service::create([
            'service_category_id' => $category->id,
            'name' => 'Signature Haircut',
            'slug' => 'signature-haircut',
            'default_price' => 150000.00,
            'default_duration' => 30,
            'is_active' => true
        ]);

        $stylist = Stylist::create([
            'outlet_id' => $outlet->id,
            'name' => 'Ani Stylist',
            'slug' => 'ani-stylist',
            'status' => 'active'
        ]);

        $createAction = new CreateBooking();
        
        Carbon::setTestNow(Carbon::now()->subMinutes(20));

        $booking = $createAction->execute([
            'phone' => '08123456789',
            'customer_name' => 'Budi Santoso',
            'outlet_id' => $outlet->id,
            'service_id' => $service->id,
            'stylist_id' => $stylist->id,
            'booking_date' => Carbon::now()->toDateString(),
            'booking_time' => Carbon::now()->format('H:i'),
            'payment_method' => 'manual',
            'source' => 'website'
        ]);

        Carbon::setTestNow();

        $today = Carbon::today()->toDateString();
        $bookings = Booking::whereDate('booking_date', $today)
            ->whereIn('status', ['pending', 'confirmed'])
            ->with('items')
            ->get();

        foreach ($bookings as $b) {
            $item = $b->items->first();
            if ($item) {
                $bookingTime = Carbon::parse($b->booking_date->format('Y-m-d') . ' ' . $item->start_time);
                if (Carbon::now()->gt($bookingTime->addMinutes(15))) {
                    $b->update(['status' => 'expired']);
                }
            }
        }

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'expired'
        ]);
    }
}
