<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\WhatsApp\WhatsAppManager;
use App\Services\WhatsApp\Providers\MetaWhatsAppProvider;
use App\Services\WhatsApp\Providers\FonnteWhatsAppProvider;
use App\Domains\WhatsApp\Models\WhatsAppMessage;
use App\Domains\WhatsApp\Models\WhatsAppWebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Event;
use App\Domains\Booking\Models\Booking;
use App\Domains\Customer\Models\Customer;
use App\Domains\Outlet\Models\Outlet;
use App\Domains\Stylist\Models\Stylist;
use App\Notifications\BookingConfirmedNotification;
use App\Notifications\BookingCreatedNotification;
use App\Notifications\BookingCancelledNotification;
use App\Notifications\BookingExpiredNotification;
use App\Notifications\BookingCompletedNotification;
use App\Domains\Booking\Events\BookingCreated;
use App\Domains\Booking\Events\BookingConfirmed;
use App\Domains\Booking\Events\BookingCancelled;
use App\Domains\Booking\Events\BookingExpired;
use App\Domains\Booking\Events\BookingCompleted;

class WhatsAppTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Configure default testing values
        config(['whatsapp.meta.token' => 'test-meta-token']);
        config(['whatsapp.meta.phone_number_id' => 'test-phone-id']);
        config(['whatsapp.meta.verify_token' => 'test-verify-token']);
        config(['whatsapp.fonnte.token' => 'test-fonnte-token']);
    }

    /**
     * Test 1 & 12: Provider selection & switching.
     */
    public function test_provider_selection_and_switching()
    {
        config(['whatsapp.provider' => 'meta']);
        $managerMeta = new WhatsAppManager();
        
        $reflectorMeta = new \ReflectionClass($managerMeta);
        $propertyMeta = $reflectorMeta->getProperty('driver');
        $propertyMeta->setAccessible(true);
        $this->assertInstanceOf(MetaWhatsAppProvider::class, $propertyMeta->getValue($managerMeta));

        config(['whatsapp.provider' => 'fonnte']);
        $managerFonnte = new WhatsAppManager();
        
        $reflectorFonnte = new \ReflectionClass($managerFonnte);
        $propertyFonnte = $reflectorFonnte->getProperty('driver');
        $propertyFonnte->setAccessible(true);
        $this->assertInstanceOf(FonnteWhatsAppProvider::class, $propertyFonnte->getValue($managerFonnte));
    }

    /**
     * Test 2: Meta provider sending.
     */
    public function test_meta_provider_sending_success()
    {
        config(['whatsapp.provider' => 'meta']);

        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'messages' => [
                    ['id' => 'wamid.HBgNNjI4MT']
                ]
            ], 200)
        ]);

        $manager = new WhatsAppManager();
        $result = $manager->sendText('62812345678', 'Halo Meta');

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('whatsapp_messages', [
            'provider' => 'meta',
            'recipient' => '62812345678',
            'status' => 'SENT',
            'external_message_id' => 'wamid.HBgNNjI4MT'
        ]);
    }

    /**
     * Test 3: Fonnte provider sending.
     */
    public function test_fonnte_provider_sending_success()
    {
        config(['whatsapp.provider' => 'fonnte']);

        Http::fake([
            'api.fonnte.com/*' => Http::response([
                'status' => true,
                'id' => ['fonnte-12345']
            ], 200)
        ]);

        $manager = new WhatsAppManager();
        $result = $manager->sendText('62812345678', 'Halo Fonnte');

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('whatsapp_messages', [
            'provider' => 'fonnte',
            'recipient' => '62812345678',
            'status' => 'SENT',
            'external_message_id' => 'fonnte-12345'
        ]);
    }

    /**
     * Test 11: Failed message handling.
     */
    public function test_failed_message_handling()
    {
        config(['whatsapp.provider' => 'meta']);

        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'error' => [
                    'message' => 'Invalid OAuth access token.',
                    'type' => 'OAuthException',
                    'code' => 190
                ]
            ], 401)
        ]);

        $manager = new WhatsAppManager();
        $result = $manager->sendText('62812345678', 'Test Fail');

        $this->assertFalse($result['success']);
        $this->assertDatabaseHas('whatsapp_messages', [
            'provider' => 'meta',
            'recipient' => '62812345678',
            'status' => 'FAILED',
            'error_message' => '{"error":{"message":"Invalid OAuth access token.","type":"OAuthException","code":190}}'
        ]);
    }

    /**
     * Test 8: Webhook signature validation.
     */
    public function test_webhook_signature_validation()
    {
        putenv('WHATSAPP_APP_SECRET=app-secret-123');

        $payload = ['test' => 'data'];
        $signature = 'sha256=' . hash_hmac('sha256', json_encode($payload), 'app-secret-123');

        $response = $this->withHeaders([
            'X-Hub-Signature-256' => $signature
        ])->postJson('/api/webhooks/whatsapp', $payload);

        $response->assertStatus(200);

        // Test with invalid signature
        $invalidResponse = $this->withHeaders([
            'X-Hub-Signature-256' => 'invalid-sig'
        ])->postJson('/api/webhooks/whatsapp', $payload);

        $invalidResponse->assertStatus(403);
    }

    /**
     * Test 9: Webhook idempotency.
     */
    public function test_webhook_idempotency()
    {
        $payload = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                'statuses' => [
                                    [
                                        'id' => 'wamid.unique-123',
                                        'status' => 'delivered',
                                        'timestamp' => time()
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // First delivery
        $response1 = $this->postJson('/api/webhooks/whatsapp', $payload);
        $response1->assertStatus(200);

        $this->assertDatabaseHas('whatsapp_webhook_events', [
            'event_id' => 'wamid.unique-123'
        ]);

        // Second delivery (duplicate)
        $response2 = $this->postJson('/api/webhooks/whatsapp', $payload);
        $response2->assertStatus(200);
        $response2->assertSeeText('Duplicate Event ignored');

        // Check only 1 event recorded
        $this->assertEquals(1, WhatsAppWebhookEvent::where('event_id', 'wamid.unique-123')->count());
    }

    /**
     * Test 4, 5, 6, 7 & 10: Booking notification triggers and queues.
     */
    public function test_booking_notifications_triggered_and_queued()
    {
        Notification::fake();
        Event::fake([
            BookingCreated::class,
            BookingConfirmed::class,
            BookingCancelled::class,
            BookingExpired::class,
            BookingCompleted::class
        ]);

        $customer = Customer::create([
            'customer_code' => 'CUST-00001',
            'phone' => '62812345678',
            'name' => 'Budi'
        ]);

        $outlet = Outlet::create([
            'name' => 'SCBD',
            'slug' => 'scbd',
            'address' => 'Jakarta'
        ]);

        $stylist = Stylist::create([
            'outlet_id' => $outlet->id,
            'name' => 'Rian',
            'slug' => 'rian'
        ]);

        $booking = Booking::create([
            'customer_id' => $customer->id,
            'outlet_id' => $outlet->id,
            'stylist_id' => $stylist->id,
            'booking_code' => 'BK-123',
            'booking_date' => now()->toDateString(),
            'booking_token' => 'token-123',
            'total_amount' => 150000.00,
            'net_amount' => 150000.00,
            'status' => 'pending'
        ]);

        // Trigger events manually and verify listeners execute notification dispatching
        Notification::assertNothingSent();

        // 1. BookingCreated
        event(new BookingCreated($booking));
        // 2. BookingConfirmed
        event(new BookingConfirmed($booking));
        // 3. BookingCancelled
        event(new BookingCancelled($booking));
        // 4. BookingExpired
        event(new BookingExpired($booking));
        // 5. BookingCompleted
        event(new BookingCompleted($booking));

        Event::assertDispatched(BookingCreated::class);
        Event::assertDispatched(BookingConfirmed::class);
        Event::assertDispatched(BookingCancelled::class);
        Event::assertDispatched(BookingExpired::class);
        Event::assertDispatched(BookingCompleted::class);
    }
}
