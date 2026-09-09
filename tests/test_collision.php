<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Domains\Booking\Services\AvailabilityService;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Models\BookingItem;
use Carbon\Carbon;

$date = '2026-09-15'; // A future date

// 1. Create test booking at 11:00 - 12:00
$b = Booking::create([
    'outlet_id' => 2,
    'stylist_id' => 2,
    'customer_id' => 1,
    'booking_date' => $date,
    'status' => 'confirmed',
    'booking_code' => 'TEST-1100',
    'booking_token' => 'token-1100',
    'total_amount' => 100000,
    'net_amount' => 100000,
    'source' => 'website'
]);

BookingItem::create([
    'booking_id' => $b->id,
    'service_id' => 5,
    'price' => 100000,
    'duration' => 60,
    'start_time' => '11:00:00',
    'end_time' => '12:00:00'
]);

$srv = new AvailabilityService();

echo "=== CHECK SLOTS ON {$date} WITH 11:00 BOOKED ===" . PHP_EOL;
$slots = $srv->getAvailableSlots(2, 2, 5, $date, false);
foreach ($slots as $s) {
    echo "Slot: {$s['time']} - {$s['end_time']} | " . ($s['is_priority'] ? "PRIORITY ({$s['badge']})" : "NORMAL") . PHP_EOL;
}

echo PHP_EOL . "=== VALIDATING INTERVALS ===" . PHP_EOL;
$check11 = $srv->isIntervalAvailable(2, 2, 5, $date, '11:00', false);
echo "11:00: " . json_encode($check11) . PHP_EOL;

$check12 = $srv->isIntervalAvailable(2, 2, 5, $date, '12:00', false);
echo "12:00: " . json_encode($check12) . PHP_EOL;

$check13 = $srv->isIntervalAvailable(2, 2, 5, $date, '13:00', false);
echo "13:00: " . json_encode($check13) . PHP_EOL;

// Clean up
$b->items()->delete();
$b->delete();
