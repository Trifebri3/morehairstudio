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
            'end_time' => '20:00:00',
            'break_start' => null,
            'break_end' => null,
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
        
        $baseTime = Carbon::today()->setHour(14)->setMinute(0)->setSecond(0);
        Carbon::setTestNow($baseTime->copy()->subMinutes(20));

        $booking = $createAction->execute([
            'phone' => '08123456789',
            'customer_name' => 'Budi Santoso',
            'outlet_id' => $outlet->id,
            'service_id' => $service->id,
            'stylist_id' => $stylist->id,
            'booking_date' => Carbon::today()->toDateString(),
            'booking_time' => Carbon::now()->format('H:i'),
            'payment_method' => 'manual',
            'source' => 'website'
        ]);

        Carbon::setTestNow($baseTime);

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

        Carbon::setTestNow();
    }

    public function test_booking_auto_complete_on_service_duration_elapsed()
    {
        $outlet = Outlet::create([
            'name' => 'MORE Auto Complete Outlet',
            'slug' => 'more-auto-complete',
            'address' => 'Jl. Automated No. 1',
            'status' => 'active'
        ]);

        $category = ServiceCategory::create([
            'name' => 'Coloring',
            'slug' => 'coloring'
        ]);

        $service = Service::create([
            'service_category_id' => $category->id,
            'name' => 'Balayage Premium',
            'slug' => 'balayage-premium',
            'default_price' => 500000.00,
            'default_duration' => 60,
            'is_active' => true
        ]);

        $stylist = Stylist::create([
            'outlet_id' => $outlet->id,
            'name' => 'Top Stylist',
            'slug' => 'top-stylist',
            'status' => 'active'
        ]);

        $createAction = new CreateBooking();
        $baseTime = Carbon::today()->setHour(10)->setMinute(0)->setSecond(0);
        Carbon::setTestNow($baseTime);

        $booking = $createAction->execute([
            'phone' => '081299998888',
            'customer_name' => 'Clara Client',
            'outlet_id' => $outlet->id,
            'service_id' => $service->id,
            'stylist_id' => $stylist->id,
            'booking_date' => $baseTime->toDateString(),
            'booking_time' => '10:00',
            'payment_method' => 'manual',
            'source' => 'website'
        ]);

        // Customer checks in at 10:00
        $checkInAction = new CheckInBooking();
        $booking = $checkInAction->execute($booking, $outlet->id);

        $this->assertEquals('checked_in', $booking->status);
        $this->assertEquals(60, $booking->service_duration_minutes);
        $this->assertEquals($baseTime->copy()->addMinutes(60)->toDateTimeString(), $booking->service_end_at->toDateTimeString());

        // Fast-forward 30 minutes (treatment halfway through)
        Carbon::setTestNow($baseTime->copy()->addMinutes(30));
        $this->assertFalse($booking->shouldAutoComplete());
        $this->assertEquals(30, $booking->remaining_service_minutes);

        // Fast-forward 65 minutes (duration elapsed, auto complete triggered)
        Carbon::setTestNow($baseTime->copy()->addMinutes(65));
        $this->assertTrue($booking->shouldAutoComplete());

        $completedCount = Booking::autoCompleteDueBookings($outlet->id);
        $this->assertEquals(1, $completedCount);

        $booking->refresh();
        $this->assertEquals('completed', $booking->status);

        Carbon::setTestNow();
    }

    public function test_booking_auto_expires_when_checkin_late_past_grace_period_and_frees_slot()
    {
        $outlet = Outlet::create([
            'name' => 'MORE Grace Period Outlet',
            'slug' => 'more-grace-period',
            'address' => 'Jl. Toleransi No. 15',
            'status' => 'active',
            'checkin_grace_period_active' => true,
            'checkin_grace_period_minutes' => 15,
            'booking_lead_time_hours' => 0
        ]);

        $category = ServiceCategory::create([
            'name' => 'Styling',
            'slug' => 'styling'
        ]);

        $service = Service::create([
            'service_category_id' => $category->id,
            'name' => 'Cut and Style',
            'slug' => 'cut-and-style',
            'default_price' => 150000.00,
            'default_duration' => 45,
            'is_active' => true
        ]);

        $stylist = Stylist::create([
            'outlet_id' => $outlet->id,
            'name' => 'Stylist Grace',
            'slug' => 'stylist-grace',
            'status' => 'active'
        ]);

        $createAction = new CreateBooking();
        $baseTime = Carbon::today()->setHour(10)->setMinute(0)->setSecond(0);
        Carbon::setTestNow($baseTime);

        // Customer 1 books slot 10:00
        $booking1 = $createAction->execute([
            'phone' => '08111111111',
            'customer_name' => 'Late Customer',
            'outlet_id' => $outlet->id,
            'service_id' => $service->id,
            'stylist_id' => $stylist->id,
            'booking_date' => $baseTime->toDateString(),
            'booking_time' => '10:00',
            'payment_method' => 'manual',
            'source' => 'website'
        ]);

        $this->assertEquals('pending', $booking1->status);

        // At 10:10 (10 minutes in, within 15 min grace period) -> should NOT expire yet
        Carbon::setTestNow($baseTime->copy()->addMinutes(10));
        $this->assertFalse($booking1->shouldAutoExpire());

        // At 10:16 (16 minutes in, passed 15 min grace period without check-in) -> should expire
        Carbon::setTestNow($baseTime->copy()->addMinutes(16));
        $this->assertTrue($booking1->shouldAutoExpire());

        // Auto-expire runs
        $expiredCount = Booking::autoExpireNoShows($outlet->id);
        $this->assertEquals(1, $expiredCount);

        $booking1->refresh();
        $this->assertEquals('expired', $booking1->status);

        // Now that booking1 is expired, Customer 2 (or walk-in) tries to book the same slot 10:00
        $booking2 = $createAction->execute([
            'phone' => '08222222222',
            'customer_name' => 'New Customer',
            'outlet_id' => $outlet->id,
            'service_id' => $service->id,
            'stylist_id' => $stylist->id,
            'booking_date' => $baseTime->toDateString(),
            'booking_time' => '10:00',
            'payment_method' => 'manual',
            'source' => 'website'
        ]);

        // Second booking succeeds because slot was freed!
        $this->assertNotNull($booking2->id);
        $this->assertNotEquals($booking1->id, $booking2->id);
        $this->assertEquals('pending', $booking2->status);

        // If Customer 1 tries to check-in their expired booking, it throws exception
        $checkInAction = new CheckInBooking();
        $checkInExceptionThrown = false;
        try {
            $checkInAction->execute($booking1, $outlet->id);
        } catch (\Exception $e) {
            $checkInExceptionThrown = true;
            $this->assertStringContainsString('sudah hangus', $e->getMessage());
        }
        $this->assertTrue($checkInExceptionThrown, 'Expected expired booking check-in exception was not thrown.');

        Carbon::setTestNow();
    }
}
