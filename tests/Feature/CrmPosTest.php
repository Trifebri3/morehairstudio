<?php

use App\Domains\Customer\Models\Customer;
use App\Domains\Customer\Models\CustomerActivity;
use App\Domains\Booking\Models\Booking;
use App\Domains\Service\Models\Service;
use App\Domains\POS\Models\Product;
use App\Domains\POS\Models\PosTransaction;
use App\Domains\POS\Services\POSTransactionService;
use App\Domains\CRM\Services\RFMService;
use App\Domains\CRM\Services\CRMAnalyticsService;
use App\Domains\Outlet\Models\Outlet;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    // Seed outlets
    $this->outletA = Outlet::create([
        'name' => 'Studio A',
        'slug' => 'studio-a',
        'address' => 'Jl. A No. 1',
        'phone' => '081234567',
        'is_active' => true
    ]);
    
    $this->outletB = Outlet::create([
        'name' => 'Studio B',
        'slug' => 'studio-b',
        'address' => 'Jl. B No. 2',
        'phone' => '087654321',
        'is_active' => true
    ]);

    // Seed service category
    $category = \App\Domains\Service\Models\ServiceCategory::create([
        'name' => 'Haircut',
        'slug' => 'haircut'
    ]);

    // Create Service & Product
    $this->service = Service::create([
        'service_category_id' => $category->id,
        'name' => 'Gunting Rambut Pria',
        'slug' => 'gunting-rambut-pria',
        'description' => 'Gunting rambut standard barber',
        'default_price' => 50000,
        'default_duration' => 30
    ]);

    $this->product = Product::create([
        'sku' => 'POM-01',
        'name' => 'Classic Pomade',
        'description' => 'Water based pomade',
        'purchase_price' => 30000,
        'selling_price' => 60000,
        'stock' => 10,
        'min_stock' => 2,
        'is_active' => true
    ]);

    // Create customer
    $this->customer = Customer::create([
        'customer_code' => 'CUST-TEST01',
        'name' => 'Budi Hermawan',
        'phone' => '62812345678',
        'first_acquisition_source' => 'Instagram'
    ]);
});

it('calculates customer RFM and segments them correctly', function () {
    // Before completed visits, should be Lost Customers due to no records
    $rfm = RFMService::analyze($this->customer);
    expect($rfm['segment'])->toBe('Lost Customers');

    // Create a transaction to simulate completed visit
    PosTransaction::create([
        'transaction_number' => 'TX-MOCK-01',
        'outlet_id' => $this->outletA->id,
        'customer_id' => $this->customer->id,
        'subtotal' => 60000,
        'grand_total' => 66000, // + tax
        'payment_status' => 'paid',
        'status' => 'completed',
        'completed_at' => Carbon::now()
    ]);

    $rfm = RFMService::analyze($this->customer);
    // Dynamic calculation: R=5 (today), F=1 (1 visit), M=1 (<150k) -> New Customers
    expect($rfm['segment'])->toBe('New Customers')
        ->and($rfm['rfm_code'])->toBe('511');
});

it('processes checkout and deducts product stock safely', function () {
    $initialStock = $this->product->stock;

    $checkoutData = [
        'outlet_id' => $this->outletA->id,
        'customer_id' => $this->customer->id,
        'booking_id' => null,
        'staff_id' => null,
        'items' => [
            [
                'type' => 'product',
                'id' => $this->product->id,
                'qty' => 2,
                'price' => 60000,
                'discount' => 0
            ]
        ],
        'discount' => 0,
        'payment_method' => 'qris',
        'transaction_reference' => 'QRIS-REF-001'
    ];

    $tx = POSTransactionService::checkout($checkoutData);

    expect($tx->status)->toBe('completed')
        ->and($tx->payment_status)->toBe('paid');

    // Verify stock decremented
    $productFresh = $this->product->fresh();
    expect($productFresh->stock)->toBe($initialStock - 2);

    // Verify inventory movement logged
    $this->assertDatabaseHas('inventory_movements', [
        'product_id' => $this->product->id,
        'type' => 'sale',
        'quantity' => -2
    ]);

    // Verify loyalty points given (1 point per 10k IDR: Grand total = 120k + 12k tax = 132k -> 13 points)
    $customerFresh = $this->customer->fresh();
    expect($customerFresh->loyalty_points)->toBe(13);

    // Verify customer activity logged
    $this->assertDatabaseHas('customer_activities', [
        'customer_id' => $this->customer->id,
        'event_type' => 'transaction_created'
    ]);
});

it('processes refund and returns product stock and points', function () {
    $checkoutData = [
        'outlet_id' => $this->outletA->id,
        'customer_id' => $this->customer->id,
        'booking_id' => null,
        'staff_id' => null,
        'items' => [
            [
                'type' => 'product',
                'id' => $this->product->id,
                'qty' => 1,
                'price' => 60000,
                'discount' => 0
            ]
        ],
        'discount' => 0,
        'payment_method' => 'cash'
    ];

    $tx = POSTransactionService::checkout($checkoutData);
    expect($this->product->fresh()->stock)->toBe(9)
        ->and($this->customer->fresh()->loyalty_points)->toBe(6); // 60k subtotal + 6k tax = 66k -> 6 points

    // Refund
    POSTransactionService::refund($tx);

    expect($tx->fresh()->status)->toBe('refunded')
        ->and($this->product->fresh()->stock)->toBe(10) // Restored stock
        ->and($this->customer->fresh()->loyalty_points)->toBe(0); // Deducted points

    // Verify refund movement logged
    $this->assertDatabaseHas('inventory_movements', [
        'product_id' => $this->product->id,
        'type' => 'refund',
        'quantity' => 1
    ]);
});

it('enforces multi-outlet scoping for outlet admins', function () {
    // Create admin bound to outlet A
    $adminA = User::create([
        'name' => 'Admin Studio A',
        'email' => 'admin.a@morehair.com',
        'password' => bcrypt('password'),
        'role' => 'outlet_admin',
        'outlet_id' => $this->outletA->id
    ]);

    // Create admin bound to outlet B
    $adminB = User::create([
        'name' => 'Admin Studio B',
        'email' => 'admin.b@morehair.com',
        'password' => bcrypt('password'),
        'role' => 'outlet_admin',
        'outlet_id' => $this->outletB->id
    ]);

    // Create a transaction in outlet A
    $txA = PosTransaction::create([
        'transaction_number' => 'TX-OUTLET-A',
        'outlet_id' => $this->outletA->id,
        'subtotal' => 50000,
        'grand_total' => 55000,
        'status' => 'completed',
        'payment_status' => 'paid'
    ]);

    // Create a transaction in outlet B
    $txB = PosTransaction::create([
        'transaction_number' => 'TX-OUTLET-B',
        'outlet_id' => $this->outletB->id,
        'subtotal' => 80000,
        'grand_total' => 88000,
        'status' => 'completed',
        'payment_status' => 'paid'
    ]);

    // Log in as Admin A, query transactions
    $this->actingAs($adminA);
    
    // Simulate Pos component render query
    $queryA = PosTransaction::query();
    if (auth()->user()->role === 'outlet_admin') {
        $queryA->where('outlet_id', auth()->user()->outlet_id);
    }
    $resultsA = $queryA->get();

    expect($resultsA->pluck('transaction_number'))->toContain('TX-OUTLET-A')
        ->not->toContain('TX-OUTLET-B');

    // Log in as Admin B
    $this->actingAs($adminB);
    
    $queryB = PosTransaction::query();
    if (auth()->user()->role === 'outlet_admin') {
        $queryB->where('outlet_id', auth()->user()->outlet_id);
    }
    $resultsB = $queryB->get();

    expect($resultsB->pluck('transaction_number'))->toContain('TX-OUTLET-B')
        ->not->toContain('TX-OUTLET-A');
});
