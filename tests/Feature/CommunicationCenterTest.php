<?php

use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Models\BookingTicket;
use App\Domains\Booking\Services\TicketGeneratorService;
use App\Domains\Customer\Models\Customer;
use App\Domains\Outlet\Models\Outlet;
use App\Domains\Stylist\Models\Stylist;
use App\Domains\WhatsApp\Models\WhatsAppConfiguration;
use App\Domains\WhatsApp\Models\WhatsAppCampaign;
use App\Domains\WhatsApp\Models\WhatsAppCampaignRecipient;
use App\Domains\WhatsApp\Services\CampaignService;
use App\Domains\WhatsApp\Services\WhatsAppManager;
use App\Domains\System\Services\CommunicationService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

    // Create a mock outlet and customer
    $this->outlet = Outlet::create([
        'name' => 'Studio Jakarta',
        'slug' => 'studio-jakarta',
        'address' => 'Jl. Jakarta No. 10',
        'phone' => '02112345678'
    ]);

    $this->customer = Customer::create([
        'customer_code' => 'CUST-BUDI',
        'name' => 'Budi Utomo',
        'phone' => '628123456789',
        'email' => 'budi@example.com',
        'whatsapp_marketing_opt_in' => true,
        'email_marketing_opt_in' => true
    ]);
});

test('Super Admin can save and switch active WhatsApp providers safely', function () {
    // 1. Save Cloud API config
    $cloudConfigData = [
        'token' => 'meta_secret_token_123',
        'phone_number_id' => '1234567890',
        'version' => 'v20.0',
        'mock' => true
    ];

    WhatsAppConfiguration::create([
        'provider' => 'cloud_api',
        'config' => Crypt::encryptString(json_encode($cloudConfigData)),
        'is_active' => true
    ]);

    // 2. Save Fonnte config
    $fonnteConfigData = [
        'token' => 'fonnte_secret_token_abc',
        'mock' => true
    ];

    WhatsAppConfiguration::create([
        'provider' => 'fonnte',
        'config' => Crypt::encryptString(json_encode($fonnteConfigData)),
        'is_active' => false
    ]);

    // Verify manager resolves Cloud API
    $provider = WhatsAppManager::getActiveProvider();
    expect($provider)->toBeInstanceOf(\App\Domains\WhatsApp\Providers\CloudApiWhatsAppProvider::class);

    // Switch active provider
    DB::transaction(function () {
        WhatsAppConfiguration::where('provider', 'cloud_api')->update(['is_active' => false]);
        WhatsAppConfiguration::where('provider', 'fonnte')->update(['is_active' => true]);
    });

    // Verify manager resolves Fonnte now
    $provider = WhatsAppManager::getActiveProvider();
    expect($provider)->toBeInstanceOf(\App\Domains\WhatsApp\Providers\FonnteWhatsAppProvider::class);
});

test('Booking Confirmation generates Ticket PDF, QR Code, and passcode correctly', function () {
    // Mock booking
    $booking = Booking::create([
        'booking_code' => 'BK-1002',
        'booking_token' => 'token-1002',
        'customer_id' => $this->customer->id,
        'outlet_id' => $this->outlet->id,
        'booking_date' => now(),
        'status' => 'confirmed',
        'source' => 'online',
        'total_amount' => 150000.00,
        'discount_amount' => 0.00,
        'net_amount' => 150000.00
    ]);

    // Generate ticket
    $ticket = TicketGeneratorService::generateForBooking($booking);

    expect($ticket->ticket_code)->not->toBeNull()
        ->and($ticket->passcode)->toHaveLength(6)
        ->and($ticket->qr_code_path)->not->toBeNull()
        ->and($ticket->pdf_path)->not->toBeNull();

    // Verify file generated in fake storage
    $cleanPdfPath = str_replace('/storage/', '', $ticket->pdf_path);
    Storage::disk('public')->assertExists($cleanPdfPath);
});

test('Campaign recipient snapshot honors marketing opt-in opt-out consent', function () {
    // Create marketing opted-out customer
    $optOutCustomer = Customer::create([
        'customer_code' => 'CUST-ANDI',
        'name' => 'Andi Wijaya',
        'phone' => '628999999999',
        'email' => 'andi@example.com',
        'whatsapp_marketing_opt_in' => false, // OPTED OUT
        'email_marketing_opt_in' => false
    ]);

    // Create a marketing campaign
    $campaign = WhatsAppCampaign::create([
        'name' => 'Promo Merdeka',
        'template_name' => 'promo_diskon',
        'recipient_type' => 'all',
        'status' => 'PENDING'
    ]);

    // Save a template
    DB::table('whatsapp_templates')->insert([
        'template_name' => 'promo_diskon',
        'language' => 'id',
        'body' => 'Promo Diskon untuk {{customer_name}}!',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]);

    // Generate recipient snapshot
    CampaignService::createRecipientSnapshot($campaign);

    // Verify Andi is skipped or recorded correctly
    $recipients = WhatsAppCampaignRecipient::where('campaign_id', $campaign->id)->get();
    expect($recipients)->toHaveCount(2); // Budi and Andi

    // Execute Campaign
    CampaignService::executeCampaign($campaign);

    // Budi should be SENT, Andi should be SKIPPED
    $recBudi = WhatsAppCampaignRecipient::where('campaign_id', $campaign->id)->where('customer_id', $this->customer->id)->first();
    $recAndi = WhatsAppCampaignRecipient::where('campaign_id', $campaign->id)->where('customer_id', $optOutCustomer->id)->first();

    expect($recBudi->status)->toBe('SENT')
        ->and($recAndi->status)->toBe('SKIPPED');
});

test('Booking confirmed triggers WhatsApp notification to stylist if phone is configured', function () {
    // Create stylist with phone number
    $stylist = Stylist::create([
        'outlet_id' => $this->outlet->id,
        'name' => 'John Stylist',
        'slug' => 'john-stylist',
        'status' => 'active',
        'phone' => '628999999123'
    ]);

    // Create booking Confirmed
    $booking = Booking::create([
        'booking_code' => 'BK-1003',
        'booking_token' => 'token-1003',
        'customer_id' => $this->customer->id,
        'outlet_id' => $this->outlet->id,
        'stylist_id' => $stylist->id,
        'booking_date' => now(),
        'status' => 'confirmed',
        'source' => 'online',
        'total_amount' => 120000.00,
        'discount_amount' => 0.00,
        'net_amount' => 120000.00
    ]);

    event(new \App\Domains\Booking\Events\BookingConfirmed($booking));

    // Verify a message log was sent to the stylist phone
    $stylistLog = \App\Domains\WhatsApp\Models\WhatsAppMessage::where('recipient', '628999999123')->first();
    expect($stylistLog)->not->toBeNull()
        ->and($stylistLog->body)->toContain('John Stylist')
        ->and($stylistLog->body)->toContain('BK-1003');
});
