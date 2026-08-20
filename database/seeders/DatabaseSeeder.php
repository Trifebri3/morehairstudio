<?php

namespace Database\Seeders;

use App\Models\User;
use App\Domains\Outlet\Models\Outlet;
use App\Domains\Service\Models\ServiceCategory;
use App\Domains\Service\Models\Service;
use App\Domains\Service\Models\OutletService;
use App\Domains\Stylist\Models\Stylist;
use App\Domains\Stylist\Models\StylistSchedule;
use App\Domains\Customer\Models\Customer;
use App\Domains\Promotion\Models\Promotion;
use App\Domains\Review\Models\Review;
use App\Domains\CMS\Models\CMSContent;
use App\Domains\SEO\Models\SEOMetadata;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Models\BookingItem;
use App\Domains\Payment\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Outlets
        $bandung = Outlet::create([
            'name' => 'MORE Hair Studio Bandung',
            'slug' => 'more-hair-studio-bandung',
            'description' => 'Our flagship cozy studio in the heart of Bandung, offering custom luxury treatments and signature styling.',
            'address' => 'Jl. Merdeka No. 45, Bandung',
            'phone' => '0224201234',
            'whatsapp' => '6281234567890',
            'latitude' => -6.917464,
            'longitude' => 107.619122,
            'opening_hours' => [
                'monday' => ['open' => '10:00', 'close' => '20:00'],
                'tuesday' => ['open' => '10:00', 'close' => '20:00'],
                'wednesday' => ['open' => '10:00', 'close' => '20:00'],
                'thursday' => ['open' => '10:00', 'close' => '20:00'],
                'friday' => ['open' => '10:00', 'close' => '21:00'],
                'saturday' => ['open' => '10:00', 'close' => '21:00'],
                'sunday' => ['open' => '10:00', 'close' => '18:00'],
            ],
            'status' => 'active',
            'gallery' => ['/images/gallery/outlet-bdg-1.jpg', '/images/gallery/outlet-bdg-2.jpg']
        ]);

        $jakarta = Outlet::create([
            'name' => 'MORE Hair Studio Jakarta',
            'slug' => 'more-hair-studio-jakarta',
            'description' => 'Our premium luxury hair studio in SCBD Jakarta, catering to elite cuts, coloring, and complete hair care.',
            'address' => 'Sudirman Central Business District (SCBD) Lot 18, Jakarta Selatan',
            'phone' => '0215151234',
            'whatsapp' => '6281234567891',
            'latitude' => -6.224131,
            'longitude' => 106.809631,
            'opening_hours' => [
                'monday' => ['open' => '09:00', 'close' => '21:00'],
                'tuesday' => ['open' => '09:00', 'close' => '21:00'],
                'wednesday' => ['open' => '09:00', 'close' => '21:00'],
                'thursday' => ['open' => '09:00', 'close' => '21:00'],
                'friday' => ['open' => '09:00', 'close' => '22:00'],
                'saturday' => ['open' => '09:00', 'close' => '22:00'],
                'sunday' => ['open' => '10:00', 'close' => '19:00'],
            ],
            'status' => 'active',
            'gallery' => ['/images/gallery/outlet-jkt-1.jpg', '/images/gallery/outlet-jkt-2.jpg']
        ]);

        // 2. Create Users (Admins and Stylists)
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@morehair.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'outlet_id' => null,
        ]);

        $bdgAdmin = User::create([
            'name' => 'Admin Bandung',
            'email' => 'bandung@morehair.com',
            'password' => Hash::make('password'),
            'role' => 'outlet_admin',
            'outlet_id' => $bandung->id,
        ]);

        $jktAdmin = User::create([
            'name' => 'Admin Jakarta',
            'email' => 'jakarta@morehair.com',
            'password' => Hash::make('password'),
            'role' => 'outlet_admin',
            'outlet_id' => $jakarta->id,
        ]);

        // 3. Create Service Categories
        $haircutCat = ServiceCategory::create(['name' => 'Haircut', 'slug' => 'haircut', 'description' => 'Precision haircuts and premium hair styling services.']);
        $treatmentCat = ServiceCategory::create(['name' => 'Hair Treatment', 'slug' => 'hair-treatment', 'description' => 'Deep nourishing treatments for healthy, shining hair.']);
        $coloringCat = ServiceCategory::create(['name' => 'Coloring & Styling', 'slug' => 'coloring-styling', 'description' => 'Balayage, highlights, full color, and chemical styling.']);

        // 4. Create Services
        $haircut = Service::create([
            'service_category_id' => $haircutCat->id,
            'name' => 'Signature Haircut',
            'slug' => 'signature-haircut',
            'description' => 'Includes premium consultation, shampoo wash, precise haircutting, and hot towel massage finishing.',
            'default_price' => 150000.00,
            'default_duration' => 45,
            'is_active' => true
        ]);

        $coloring = Service::create([
            'service_category_id' => $coloringCat->id,
            'name' => 'Balayage Premium',
            'slug' => 'balayage-premium',
            'description' => 'Customized French hand-painted hair highlights for a soft, natural, and low-maintenance look.',
            'default_price' => 850000.00,
            'default_duration' => 150,
            'is_active' => true
        ]);

        $treatment = Service::create([
            'service_category_id' => $treatmentCat->id,
            'name' => 'Keratin Smooth Therapy',
            'slug' => 'keratin-smooth-therapy',
            'description' => 'Advanced protein treatment to eliminate frizz, restore shine, and straighten curly or damaged hair.',
            'default_price' => 600000.00,
            'default_duration' => 120,
            'is_active' => true
        ]);

        $spa = Service::create([
            'service_category_id' => $treatmentCat->id,
            'name' => 'Luxury Hair Spa & Blow',
            'slug' => 'luxury-hair-spa-blow',
            'description' => 'Relaxing hair spa session using premium scalp serums, shoulder massage, and signature blowout styling.',
            'default_price' => 250000.00,
            'default_duration' => 60,
            'is_active' => true
        ]);

        // Apply templates / overrides
        // Bandung uses default prices
        OutletService::create(['outlet_id' => $bandung->id, 'service_id' => $haircut->id, 'price' => 150000.00, 'duration' => 45, 'is_active' => true]);
        OutletService::create(['outlet_id' => $bandung->id, 'service_id' => $coloring->id, 'price' => 800000.00, 'duration' => 150, 'is_active' => true]); // Promo price in Bdg
        OutletService::create(['outlet_id' => $bandung->id, 'service_id' => $treatment->id, 'price' => 600000.00, 'duration' => 120, 'is_active' => true]);
        OutletService::create(['outlet_id' => $bandung->id, 'service_id' => $spa->id, 'price' => 220000.00, 'duration' => 60, 'is_active' => true]); // Overridden cheaper in Bdg

        // Jakarta uses slightly higher luxury pricing
        OutletService::create(['outlet_id' => $jakarta->id, 'service_id' => $haircut->id, 'price' => 180000.00, 'duration' => 45, 'is_active' => true]); // Higher in Jkt
        OutletService::create(['outlet_id' => $jakarta->id, 'service_id' => $coloring->id, 'price' => 950000.00, 'duration' => 180, 'is_active' => true]); // Higher / longer in Jkt
        OutletService::create(['outlet_id' => $jakarta->id, 'service_id' => $treatment->id, 'price' => 700000.00, 'duration' => 120, 'is_active' => true]);
        OutletService::create(['outlet_id' => $jakarta->id, 'service_id' => $spa->id, 'price' => 280000.00, 'duration' => 60, 'is_active' => true]);

        // 5. Create Stylists
        $budiUser = User::create([
            'name' => 'Budi Hermawan',
            'email' => 'budi@morehair.com',
            'password' => Hash::make('password'),
            'role' => 'stylist',
            'outlet_id' => $bandung->id,
        ]);
        $budi = Stylist::create([
            'outlet_id' => $bandung->id,
            'user_id' => $budiUser->id,
            'name' => 'Budi Hermawan',
            'slug' => 'budi-hermawan',
            'photo' => 'budi.jpg',
            'bio' => 'Signature haircutting maestro with over 8 years of styling experience.',
            'specialization' => 'Classic & Modern Cuts',
            'rating' => 4.9,
            'status' => 'active'
        ]);

        $aniUser = User::create([
            'name' => 'Ani Wijaya',
            'email' => 'ani@morehair.com',
            'password' => Hash::make('password'),
            'role' => 'stylist',
            'outlet_id' => $bandung->id,
        ]);
        $ani = Stylist::create([
            'outlet_id' => $bandung->id,
            'user_id' => $aniUser->id,
            'name' => 'Ani Wijaya',
            'slug' => 'ani-wijaya',
            'photo' => 'ani.jpg',
            'bio' => 'Coloring expert specializing in beautiful pastels, ombre, and keratin therapies.',
            'specialization' => 'Balayage & Coloring',
            'rating' => 4.85,
            'status' => 'active'
        ]);

        $johnUser = User::create([
            'name' => 'John Doe',
            'email' => 'john@morehair.com',
            'password' => Hash::make('password'),
            'role' => 'stylist',
            'outlet_id' => $jakarta->id,
        ]);
        $john = Stylist::create([
            'outlet_id' => $jakarta->id,
            'user_id' => $johnUser->id,
            'name' => 'John Doe',
            'slug' => 'john-doe',
            'photo' => 'john.jpg',
            'bio' => 'Senior stylist trained in Tokyo and London, offering precision haircuts.',
            'specialization' => 'Avant-Garde Haircuts',
            'rating' => 4.95,
            'status' => 'active'
        ]);

        $rinaUser = User::create([
            'name' => 'Rina Sugiarto',
            'email' => 'rina@morehair.com',
            'password' => Hash::make('password'),
            'role' => 'stylist',
            'outlet_id' => $jakarta->id,
        ]);
        $rina = Stylist::create([
            'outlet_id' => $jakarta->id,
            'user_id' => $rinaUser->id,
            'name' => 'Rina Sugiarto',
            'slug' => 'rina-sugiarto',
            'photo' => 'rina.jpg',
            'bio' => 'Scalp health practitioner and treatments specialist.',
            'specialization' => 'Luxury Hair Spa & Treat',
            'rating' => 4.80,
            'status' => 'active'
        ]);

        // Create Schedules for each stylist (Day 1 to 6 = Mon to Sat)
        foreach ([$budi, $ani, $john, $rina] as $stylist) {
            for ($day = 1; $day <= 6; $day++) {
                StylistSchedule::create([
                    'stylist_id' => $stylist->id,
                    'day_of_week' => $day,
                    'start_time' => '10:00:00',
                    'end_time' => '19:00:00',
                    'break_start' => '13:00:00',
                    'break_end' => '14:00:00',
                    'is_working' => true
                ]);
            }
            // Sunday (0) is day off
            StylistSchedule::create([
                'stylist_id' => $stylist->id,
                'day_of_week' => 0,
                'start_time' => '10:00:00',
                'end_time' => '18:00:00',
                'is_working' => false
            ]);
        }

        // 6. Create Promotions
        Promotion::create([
            'promo_code' => 'WELCOME10',
            'discount_type' => 'percentage',
            'discount_value' => 10.00,
            'minimum_transaction' => 100000.00,
            'maximum_discount' => 50000.00,
            'start_at' => Carbon::now()->subDays(10),
            'end_at' => Carbon::now()->addDays(90),
            'usage_limit' => 500,
            'usage_count' => 12
        ]);

        Promotion::create([
            'promo_code' => 'MORE50',
            'discount_type' => 'percentage',
            'discount_value' => 50.00,
            'minimum_transaction' => 150000.00,
            'maximum_discount' => 75000.00,
            'start_at' => Carbon::now()->subDays(2),
            'end_at' => Carbon::now()->addDays(30),
            'usage_limit' => 100,
            'usage_count' => 3
        ]);

        Promotion::create([
            'promo_code' => 'BIRTHDAYPROMO',
            'discount_type' => 'fixed',
            'discount_value' => 50000.00,
            'minimum_transaction' => 0.00,
            'maximum_discount' => 50000.00,
            'start_at' => Carbon::now()->subDays(30),
            'end_at' => Carbon::now()->addDays(300),
            'usage_limit' => null,
            'usage_count' => 5
        ]);

        Promotion::create([
            'promo_code' => 'EXPIRED20',
            'discount_type' => 'percentage',
            'discount_value' => 20.00,
            'minimum_transaction' => 100000.00,
            'start_at' => Carbon::now()->subDays(50),
            'end_at' => Carbon::now()->subDays(5),
            'usage_limit' => 200,
            'usage_count' => 200
        ]);

        // 7. Create Dummy Customers
        $c1 = Customer::create([
            'customer_code' => 'CUST-00001',
            'phone' => '6281234567890',
            'whatsapp_phone' => '6281234567890',
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@gmail.com',
            'birth_date' => '1995-08-18', // Today is birthday!
            'gender' => 'male'
        ]);

        $c2 = Customer::create([
            'customer_code' => 'CUST-00002',
            'phone' => '6287777771234',
            'whatsapp_phone' => '6287777771234',
            'name' => 'Siti Aminah',
            'email' => 'siti.aminah@yahoo.com',
            'birth_date' => '1998-02-12',
            'gender' => 'female'
        ]);

        // 8. Create some historical bookings
        $b1 = Booking::create([
            'booking_code' => 'MOR-180826-A1B2C',
            'booking_token' => Str::random(32),
            'customer_id' => $c1->id,
            'outlet_id' => $bandung->id,
            'stylist_id' => $budi->id,
            'booking_date' => Carbon::now()->subDays(2)->toDateString(),
            'status' => 'completed',
            'source' => 'website',
            'total_amount' => 150000.00,
            'discount_amount' => 15000.00,
            'net_amount' => 135000.00,
            'promo_code' => 'WELCOME10',
            'notes' => 'Pre-booked online haircut.'
        ]);

        BookingItem::create([
            'booking_id' => $b1->id,
            'service_id' => $haircut->id,
            'price' => 150000.00,
            'duration' => 45,
            'start_time' => '11:00:00',
            'end_time' => '11:45:00',
        ]);

        Payment::create([
            'booking_id' => $b1->id,
            'payment_method' => 'manual',
            'transaction_reference' => 'TX-123456',
            'amount' => 135000.00,
            'status' => 'paid',
            'paid_at' => Carbon::now()->subDays(2),
        ]);

        Review::create([
            'booking_id' => $b1->id,
            'customer_id' => $c1->id,
            'outlet_id' => $bandung->id,
            'stylist_id' => $budi->id,
            'rating' => 5,
            'review' => 'Layanan potong rambut Budi sangat rapi dan ramah. Recommended!',
            'status' => 'approved'
        ]);

        // Booking 2: Jakarta booking for Siti
        $b2 = Booking::create([
            'booking_code' => 'MOR-180826-X9Y8Z',
            'booking_token' => Str::random(32),
            'customer_id' => $c2->id,
            'outlet_id' => $jakarta->id,
            'stylist_id' => $rina->id,
            'booking_date' => Carbon::now()->addDays(2)->toDateString(),
            'status' => 'confirmed',
            'source' => 'website',
            'total_amount' => 280000.00,
            'discount_amount' => 0.00,
            'net_amount' => 280000.00,
            'notes' => 'Needs deep conditioning for scalp.'
        ]);

        BookingItem::create([
            'booking_id' => $b2->id,
            'service_id' => $spa->id,
            'price' => 280000.00,
            'duration' => 60,
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
        ]);

        Payment::create([
            'booking_id' => $b2->id,
            'payment_method' => 'midtrans',
            'transaction_reference' => 'MID-99281',
            'amount' => 280000.00,
            'status' => 'paid',
            'paid_at' => Carbon::now(),
        ]);

        // 9. Seeding CMS Content
        CMSContent::create(['key' => 'home_hero_title', 'value' => 'ELEGANCE DEFINED BY HAIR']);
        CMSContent::create(['key' => 'home_hero_subtitle', 'value' => 'Experience high-fidelity, custom hair engineering tailored to your personal aesthetic at MORE Hair Studio.']);
        CMSContent::create(['key' => 'home_about_text', 'value' => 'At MORE Hair Studio, we treat your hair as a canvas. With world-class trained stylists, custom rituals, and high-performance products, we redefine hair fashion.']);

        // 10. Seeding SEO Metadata
        SEOMetadata::create([
            'path' => '/',
            'meta_title' => 'MORE Hair Studio | Luxury Hair Dressing & Styling',
            'meta_description' => 'Book premium haircuts, coloring, and keratin therapies at MORE Hair Studio Bandung and Jakarta. Precision cuts and curated rituals.',
            'canonical_url' => 'https://morehairstudio.com/',
            'og_title' => 'MORE Hair Studio | Luxury Hair Care',
            'og_description' => 'Premium haircutting and treatments in Bandung and Jakarta SCBD.',
            'og_image' => 'https://morehairstudio.com/images/og-main.jpg'
        ]);

        // 11. Seeding Settings Configurations
        $settings = [
            // General settings
            [
                'group' => 'general',
                'key' => 'app.name',
                'label' => 'Nama Aplikasi',
                'value' => env('APP_NAME', 'MORE Hair Studio'),
                'type' => 'text',
                'options' => null,
                'description' => 'Nama aplikasi utama yang ditampilkan di website dan dashboard.',
            ],
            [
                'group' => 'general',
                'key' => 'app.url',
                'label' => 'URL Aplikasi',
                'value' => env('APP_URL', 'http://localhost'),
                'type' => 'text',
                'options' => null,
                'description' => 'URL domain utama aplikasi untuk link redirect dan webhooks.',
            ],

            // WhatsApp settings
            [
                'group' => 'whatsapp',
                'key' => 'whatsapp.provider',
                'label' => 'WhatsApp Provider',
                'value' => env('WHATSAPP_PROVIDER', 'meta'),
                'type' => 'select',
                'options' => ['meta' => 'Meta Cloud API', 'fonnte' => 'Fonnte Gateway'],
                'description' => 'Penyedia layanan pengiriman pesan WhatsApp yang aktif.',
            ],
            [
                'group' => 'whatsapp',
                'key' => 'whatsapp.meta.token',
                'label' => 'Meta Cloud API Token',
                'value' => env('WHATSAPP_TOKEN'),
                'type' => 'password',
                'options' => null,
                'description' => 'Token akses permanen/sementara dari Meta Developer console.',
            ],
            [
                'group' => 'whatsapp',
                'key' => 'whatsapp.meta.phone_number_id',
                'label' => 'Meta Phone Number ID',
                'value' => env('WHATSAPP_PHONE_NUMBER_ID'),
                'type' => 'text',
                'options' => null,
                'description' => 'ID nomor telepon pengirim dari Meta Developer console.',
            ],
            [
                'group' => 'whatsapp',
                'key' => 'whatsapp.meta.version',
                'label' => 'Meta API Version',
                'value' => env('WHATSAPP_VERSION', 'v20.0'),
                'type' => 'text',
                'options' => null,
                'description' => 'Versi API Graph Meta yang digunakan.',
            ],
            [
                'group' => 'whatsapp',
                'key' => 'whatsapp.meta.verify_token',
                'label' => 'Meta Webhook Verify Token',
                'value' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN'),
                'type' => 'text',
                'options' => null,
                'description' => 'Token verifikasi yang dikonfigurasi pada webhook Meta App.',
            ],
            [
                'group' => 'whatsapp',
                'key' => 'whatsapp.fonnte.token',
                'label' => 'Fonnte Token',
                'value' => env('FONNTE_TOKEN'),
                'type' => 'password',
                'options' => null,
                'description' => 'Token otentikasi dari Fonnte dashboard.',
            ],

            // WhatsApp notifications
            [
                'group' => 'whatsapp_notifications',
                'key' => 'whatsapp.notifications.booking_confirmation',
                'label' => 'Notifikasi Konfirmasi Booking',
                'value' => 'true',
                'type' => 'boolean',
                'options' => null,
                'description' => 'Kirim pesan WhatsApp otomatis ketika booking baru dibuat.',
            ],
            [
                'group' => 'whatsapp_notifications',
                'key' => 'whatsapp.notifications.booking_reminder',
                'label' => 'Notifikasi Pengingat Booking',
                'value' => 'true',
                'type' => 'boolean',
                'options' => null,
                'description' => 'Kirim pengingat WhatsApp otomatis sebelum jadwal booking.',
            ],
            [
                'group' => 'whatsapp_notifications',
                'key' => 'whatsapp.notifications.booking_expired',
                'label' => 'Notifikasi Booking Expired',
                'value' => 'true',
                'type' => 'boolean',
                'options' => null,
                'description' => 'Kirim pesan WhatsApp ketika waktu pembayaran booking habis.',
            ],
            [
                'group' => 'whatsapp_notifications',
                'key' => 'whatsapp.notifications.booking_cancelled',
                'label' => 'Notifikasi Booking Dibatalkan',
                'value' => 'true',
                'type' => 'boolean',
                'options' => null,
                'description' => 'Kirim pesan WhatsApp otomatis saat booking dibatalkan.',
            ],
            [
                'group' => 'whatsapp_notifications',
                'key' => 'whatsapp.notifications.booking_completed',
                'label' => 'Notifikasi Booking Selesai',
                'value' => 'true',
                'type' => 'boolean',
                'options' => null,
                'description' => 'Kirim pesan ucapan terima kasih setelah layanan selesai.',
            ],

            // Midtrans Settings
            [
                'group' => 'payment',
                'key' => 'services.midtrans.server_key',
                'label' => 'Midtrans Server Key',
                'value' => env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-yourkeyhere'),
                'type' => 'password',
                'options' => null,
                'description' => 'Server Key dari Midtrans dashboard (Sandbox/Production).',
            ],
            [
                'group' => 'payment',
                'key' => 'services.midtrans.client_key',
                'label' => 'Midtrans Client Key',
                'value' => env('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-yourkeyhere'),
                'type' => 'text',
                'options' => null,
                'description' => 'Client Key dari Midtrans dashboard (Sandbox/Production).',
            ],
            [
                'group' => 'payment',
                'key' => 'services.midtrans.is_production',
                'label' => 'Midtrans Production Mode',
                'value' => env('MIDTRANS_IS_PRODUCTION', false) ? 'true' : 'false',
                'type' => 'boolean',
                'options' => null,
                'description' => 'Aktifkan jika ingin menggunakan environment production Midtrans.',
            ],
        ];

        foreach ($settings as $setting) {
            \App\Domains\System\Models\Setting::create([
                'group' => $setting['group'],
                'key' => $setting['key'],
                'label' => $setting['label'],
                'value' => $setting['value'],
                'type' => $setting['type'],
                'options' => $setting['options'],
                'description' => $setting['description'],
            ]);
        }
    }
}
