<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WhatsAppAutomationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Create or Update Default Customer Template
            $customerTemplateBody = 'Halo {{customer_name}},
Reservasi Anda di {{outlet_name}} telah dikonfirmasi! 🎉

Kode Booking: {{booking_code}}
Layanan: {{service_name}}
Tanggal: {{booking_date}}
Pukul: {{booking_time}}
Hairstylist: {{barber_name}}

Tiket masuk: {{ticket_url}}
Silakan tunjukkan QR Code pada saat kedatangan.';

            DB::table('whatsapp_templates')->updateOrInsert(
                ['template_name' => 'customer_booking_confirmed'],
                [
                    'language' => 'id',
                    'body' => $customerTemplateBody,
                    'variables' => json_encode(['customer_name', 'outlet_name', 'booking_code', 'service_name', 'booking_date', 'booking_time', 'barber_name', 'ticket_url']),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // 2. Create Default Stylist Template
            $stylistTemplateBody = 'Halo {{barber_name}}, Anda memiliki reservasi baru:
Kode Booking: {{booking_code}}
Pelanggan: {{customer_name}}
Layanan: {{service_name}}
Tanggal: {{booking_date}}
Pukul: {{booking_time}}

Mohon bersiap-siap melayani pelanggan.';

            DB::table('whatsapp_templates')->updateOrInsert(
                ['template_name' => 'stylist_new_booking'],
                [
                    'language' => 'id',
                    'body' => $stylistTemplateBody,
                    'variables' => json_encode(['barber_name', 'booking_code', 'customer_name', 'service_name', 'booking_date', 'booking_time']),
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // 3. Automation for Customer (Confirmed) with QR
            DB::table('whatsapp_automations')->updateOrInsert(
                [
                    'name' => 'Kirim Tiket Pelanggan',
                    'event_type' => 'BOOKING_CONFIRMED',
                    'recipient' => 'customer',
                ],
                [
                    'template_name' => 'customer_booking_confirmed',
                    'include_qr' => true,
                    'is_active' => true,
                    'delay_minutes' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // 4. Automation for Stylist (Created/Confirmed) without QR
            DB::table('whatsapp_automations')->updateOrInsert(
                [
                    'name' => 'Notifikasi ke Hairstylist',
                    'event_type' => 'BOOKING_CONFIRMED',
                    'recipient' => 'stylist',
                ],
                [
                    'template_name' => 'stylist_new_booking',
                    'include_qr' => false,
                    'is_active' => true,
                    'delay_minutes' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        });
    }
}
