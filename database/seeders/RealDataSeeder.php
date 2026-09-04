<?php

namespace Database\Seeders;

use App\Domains\Outlet\Models\Outlet;
use App\Domains\Service\Models\Service;
use App\Domains\Service\Models\ServiceCategory;
use App\Domains\Stylist\Models\Stylist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RealDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create or Update Outlet
        $schedule = [
            'monday' => ['is_open' => true, 'open' => '10:00', 'close' => '20:00'],
            'tuesday' => ['is_open' => true, 'open' => '10:00', 'close' => '20:00'],
            'wednesday' => ['is_open' => true, 'open' => '10:00', 'close' => '20:00'],
            'thursday' => ['is_open' => true, 'open' => '10:00', 'close' => '20:00'],
            'friday' => ['is_open' => true, 'open' => '10:00', 'close' => '20:00'],
            'saturday' => ['is_open' => true, 'open' => '10:00', 'close' => '20:00'],
            'sunday' => ['is_open' => true, 'open' => '10:00', 'close' => '20:00'],
        ];

        $outlet = Outlet::updateOrCreate(
            ['slug' => 'more-hair-studio'],
            [
                'name' => 'MORE Hair Studio',
                'description' => 'Urban barbershop & creative ecosystem di jantung Kota Bandung dengan filosofi Human-Hair Centered Design. Ruang tanpa batas bagi setiap individu untuk mengekspresikan identitas diri melampaui tren.',
                'address' => 'Jl. Mangga No. 37A, Cihapit, Bandung, Jawa Barat 40114',
                'phone' => '082298347730',
                'whatsapp' => '6282298347730',
                'opening_hours' => $schedule,
                'status' => 'active',
            ]
        );

        // 2. Create Stylists
        $stylists = [
            [
                'name' => 'Angga Pujangga',
                'slug' => 'angga',
                'specialization' => 'Hair Artist',
                'ig' => '@anggapjnur',
                'tiktok' => '@anggapjnur14',
                'bio' => 'Menghadirkan seni potong presisi dan eksplorasi tekstur rambut berbasis karakter personal.',
            ],
            [
                'name' => 'HeyDud',
                'slug' => 'heydud',
                'specialization' => 'Senior Barber',
                'ig' => '@dudi.yanuari',
                'tiktok' => '@dudiyanuari',
                'bio' => 'Spesialis potongan tajam, siluet arsitektural, dan tekstur rambut kasual effortless.',
            ],
            [
                'name' => 'Moo',
                'slug' => 'moo',
                'specialization' => 'Barber',
                'ig' => '@mohmmdrzkym_',
                'tiktok' => '@mohammadrizkymaulana',
                'bio' => 'Mengutamakan kenyamanan, teknik detail rapi, dan perawatan tekstur alami.',
            ],
        ];

        foreach ($stylists as $stData) {
            Stylist::updateOrCreate(
                ['slug' => $stData['slug']],
                [
                    'outlet_id' => $outlet->id,
                    'name' => $stData['name'],
                    'specialization' => $stData['specialization'],
                    'instagram' => $stData['ig'],
                    'tiktok' => $stData['tiktok'],
                    'bio' => $stData['bio'],
                    'status' => 'active',
                ]
            );
        }

        // 3. Create Categories & Services
        $categories = [
            'Haircut' => [
                ['name' => 'Haircut', 'duration' => 60, 'price' => 100000, 'desc' => 'Termasuk Define Session 10 menit, pemotongan presisi sesuai bentuk wajah, relaxing wash, dan styling.'],
                ['name' => 'Long Hair Additional', 'duration' => 20, 'price' => 25000, 'desc' => 'Tambahan penyesuaian khusus untuk pengerjaan tekstur rambut panjang (+25k).'],
                ['name' => 'Skin Fade Additional', 'duration' => 15, 'price' => 10000, 'desc' => 'Tambahan teknik gradasi halus ultra-presisi skin fade (+10k).'],
            ],
            'Chemical Package' => [
                ['name' => 'Smooth Flow Keratin + Haircut', 'duration' => 180, 'price' => 499000, 'desc' => 'Perawatan keratin menutrisi untuk tekstur rambut lurus alami, dipadukan pemotongan presisi, wash, dan blow dry.'],
                ['name' => 'Design Perm + Haircut', 'duration' => 180, 'price' => 499000, 'desc' => 'Menciptakan tekstur volume ikal natural dan mudah diatur dengan garansi re-touch, dipadukan dengan potongan presisi.'],
                ['name' => 'Down Perm & Root Lift + Haircut', 'duration' => 120, 'price' => 299000, 'desc' => 'Bundling peramping rambut samping yang jigrak (Down Perm) sekaligus mengangkat volume atas (Root Lift) + Haircut.'],
            ],
            'Urban Exploration' => [
                ['name' => 'Hair Coloring', 'duration' => 120, 'price' => 250000, 'desc' => 'Eksplorasi warna artistik urban berkarakter selaras dengan skin tone.'],
                ['name' => 'Cornrows & Braids', 'duration' => 120, 'price' => 350000, 'desc' => 'Eksplorasi gaya rambut kepang kreatif kontemporer.'],
            ],
        ];

        foreach ($categories as $catName => $services) {
            $category = ServiceCategory::updateOrCreate(
                ['name' => $catName],
                ['slug' => Str::slug($catName), 'description' => $catName]
            );

            foreach ($services as $srvData) {
                $service = Service::updateOrCreate(
                    ['name' => $srvData['name']],
                    [
                        'slug' => Str::slug($srvData['name']),
                        'description' => $srvData['desc'] ?? $srvData['name'],
                        'service_category_id' => $category->id,
                        'default_price' => $srvData['price'],
                        'default_duration' => $srvData['duration'],
                    ]
                );

                // Attach to outlet
                DB::table('outlet_services')->updateOrInsert(
                    ['outlet_id' => $outlet->id, 'service_id' => $service->id],
                    [
                        'price' => $srvData['price'],
                        'duration' => $srvData['duration'],
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
