<?php

namespace App\Domains\SEO\Services;

use App\Domains\SEO\Models\SEOMetadata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class SEOService
{
    /**
     * Resolve complete SEO metadata for the current or specified path.
     */
    public static function getMetadata(?string $path = null, array $custom = []): array
    {
        $path = $path ?? ('/' . ltrim(request()->path(), '/'));
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        // 1. Check if an explicit override exists in the database
        $dbSeo = SEOMetadata::where('path', $path)->first();

        // 2. Base defaults optimized specifically for Bandung Local SEO
        $defaultTitle = 'MORE Hair Studio | Barbershop & Hair Salon Premium Bandung';
        $defaultDesc = 'MORE Hair Studio Bandung adalah studio potong rambut pria & salon grooming premium di Cihapit Bandung. Nikmati layanan haircut modern, hair perm, coloring, dan custom rituals terbaik di Bandung.';
        $defaultKeywords = 'barbershop bandung, hair studio bandung, potong rambut pria bandung, hair perm bandung, barbershop cihapit, tempat potong rambut bandung, hair coloring bandung, barber shop terbaik di bandung, salon pria bandung, more hair studio';
        $defaultImage = asset('images/og-more-studio.jpg');
        $defaultType = 'website';

        $routeName = Route::currentRouteName();

        // 3. Intelligent path / route fallbacks
        if ($path === '/' || $routeName === 'home') {
            $title = 'MORE Hair Studio | Barbershop & Hair Salon Premium Bandung';
            $desc = 'MORE Hair Studio Bandung - Studio grooming & barbershop premium di Cihapit Kota Bandung. Nikmati haircut presisi, hair perm, coloring, dan custom rituals terbaik.';
        } elseif ($routeName === 'about' || str_starts_with($path, '/about')) {
            $title = 'Tentang Kami | MORE Hair Studio Bandung';
            $desc = 'Mengenal MORE Hair Studio Bandung - perpaduan seni potong rambut, grooming presisi, dan kultur kreatif urban di Cihapit Kota Bandung.';
        } elseif ($routeName === 'services.index' || str_starts_with($path, '/services')) {
            $title = 'Daftar Layanan & Harga Potong Rambut Bandung | MORE Hair Studio';
            $desc = 'Daftar harga & menu layanan haircut, hair perm, coloring, scalp treatment, dan grooming ritual pria di MORE Hair Studio Bandung. Harga transparan & hasil memukau.';
        } elseif ($routeName === 'stylists.index' || str_starts_with($path, '/stylists')) {
            $title = 'Hair Stylists & Barbers Terbaik Bandung | MORE Hair Studio';
            $desc = 'Temui jajaran hair artist & barbers profesional di MORE Hair Studio Bandung. Konsultasikan gaya rambut trendi dan pesan jadwal potong rambut sekarang.';
        } elseif ($routeName === 'outlets.index' || str_starts_with($path, '/outlets')) {
            $title = 'Lokasi Studio Barbershop Cihapit Bandung | MORE Hair Studio';
            $desc = 'Kunjungi studio MORE Hair Studio di Jl. Mangga No. 37A Cihapit Bandung Wetan. Suasana nyaman berkonsep estetik dengan fasilitas luxury grooming terlengkap.';
        } elseif (in_array($routeName, ['schedule.index', 'schedule.alias']) || str_starts_with($path, '/jadwal') || str_starts_with($path, '/schedule')) {
            $title = 'Jadwal & Ketersediaan Hair Stylist | MORE Hair Studio Bandung';
            $desc = 'Cek jadwal kehadiran hair artist MORE Hair Studio Bandung hari ini. Temukan slot kosong dan reservasi secara instan tanpa antre.';
        } elseif (str_starts_with($routeName ?? '', 'booking') || str_starts_with($path, '/booking')) {
            $title = 'Reservasi Online Potong Rambut Bandung | MORE Hair Studio';
            $desc = 'Booking jadwal potong rambut, hair perm, atau coloring di MORE Hair Studio Bandung secara online. Pilih stylist favorit, tanggal, dan waktu secara fleksibel.';
        } elseif ($routeName === 'terms' || str_starts_with($path, '/terms')) {
            $title = 'Syarat & Ketentuan Layanan | MORE Hair Studio Bandung';
            $desc = 'Syarat dan ketentuan pemesanan reservasi serta layanan di MORE Hair Studio Bandung.';
        } elseif ($routeName === 'privacy' || str_starts_with($path, '/privacy')) {
            $title = 'Kebijakan Privasi | MORE Hair Studio Bandung';
            $desc = 'Kebijakan privasi dan perlindungan data pelanggan di MORE Hair Studio Bandung.';
        } else {
            $title = $defaultTitle;
            $desc = $defaultDesc;
        }

        // Apply Database overrides if configured in admin panel
        if ($dbSeo) {
            $title = $dbSeo->meta_title ?: $title;
            $desc = $dbSeo->meta_description ?: $desc;
            if (!empty($dbSeo->og_image)) {
                $defaultImage = str_starts_with($dbSeo->og_image, 'http') ? $dbSeo->og_image : asset($dbSeo->og_image);
            }
        }

        // Apply View / Controller custom overrides
        $finalTitle = $custom['title'] ?? $title;
        $finalDesc = $custom['description'] ?? $desc;
        $finalKeywords = $custom['keywords'] ?? $defaultKeywords;
        $finalImage = $custom['image'] ?? $defaultImage;
        $finalType = $custom['type'] ?? $defaultType;
        $finalUrl = $custom['url'] ?? url()->current();
        $canonical = $dbSeo->canonical_url ?? $custom['canonical'] ?? $finalUrl;

        // Ensure image URL is always absolute
        if (!str_starts_with($finalImage, 'http://') && !str_starts_with($finalImage, 'https://')) {
            $finalImage = url($finalImage);
        }

        return [
            'title' => $finalTitle,
            'description' => $finalDesc,
            'keywords' => $finalKeywords,
            'canonical' => $canonical,
            'image' => $finalImage,
            'type' => $finalType,
            'url' => $finalUrl,
            'site_name' => 'MORE Hair Studio',
            'locale' => app()->getLocale() === 'en' ? 'en_US' : 'id_ID',
            'geo' => [
                'region' => 'ID-JB',
                'placename' => 'Bandung, Jawa Barat, Indonesia',
                'position' => '-6.911558;107.623485',
                'icbm' => '-6.911558, 107.623485',
            ],
            'schema' => self::generateLocalBusinessSchema($custom),
        ];
    }

    /**
     * Generate Schema.org LocalBusiness (HairSalon) JSON-LD for Bandung Local SEO
     */
    public static function generateLocalBusinessSchema(array $custom = []): array
    {
        $baseUrl = url('/');
        $logoUrl = asset('logo/logo.png');
        $imageUrl = asset('images/og-more-studio.jpg');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'HairSalon',
            '@id' => $baseUrl . '/#hairsalon',
            'name' => 'MORE Hair Studio Bandung',
            'alternateName' => ['MORE Hair Studio', 'MORE Barbershop Bandung', 'MORE Barbershop Cihapit'],
            'url' => $baseUrl,
            'logo' => $logoUrl,
            'image' => [
                $imageUrl,
                asset('images/more_studio_interior.jpg'),
                asset('images/hero_haircut.jpg'),
            ],
            'description' => 'Studio potong rambut pria & salon grooming modern premium di Cihapit Bandung. Menyediakan layanan haircut, hair perm, hair coloring, dan custom rituals.',
            'telephone' => '+6282298347730',
            'priceRange' => 'Rp 100.000 - Rp 500.000',
            'currenciesAccepted' => 'IDR',
            'paymentAccepted' => 'Cash, QRIS, Debit Card, Credit Card',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Jl. Mangga No. 37A, Cihapit',
                'addressLocality' => 'Bandung',
                'addressRegion' => 'Jawa Barat',
                'postalCode' => '40114',
                'addressCountry' => 'ID',
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => -6.911558,
                'longitude' => 107.623485,
            ],
            'hasMap' => 'https://maps.google.com/?q=-6.911558,107.623485',
            'openingHoursSpecification' => [
                [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => [
                        'Monday',
                        'Tuesday',
                        'Wednesday',
                        'Thursday',
                        'Friday',
                        'Saturday',
                        'Sunday'
                    ],
                    'opens' => '09:00',
                    'closes' => '21:00',
                ]
            ],
            'sameAs' => [
                'https://www.instagram.com/morehairstudio/',
                'https://www.tiktok.com/@morehairstudio',
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '4.9',
                'bestRating' => '5.0',
                'worstRating' => '1.0',
                'reviewCount' => '185',
            ],
            'potentialAction' => [
                '@type' => 'ReserveAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => route('booking.index'),
                    'inLanguage' => 'id-ID',
                    'actionPlatform' => [
                        'http://schema.org/DesktopWebPlatform',
                        'http://schema.org/MobileWebPlatform',
                    ],
                ],
                'result' => [
                    '@type' => 'Reservation',
                    'name' => 'Reservasi Potong Rambut MORE Hair Studio',
                ],
            ],
        ];

        // If specific stylist data is provided, nest Person schema
        if (!empty($custom['stylist'])) {
            $stylist = $custom['stylist'];
            $schema['employee'] = [
                '@type' => 'Person',
                'name' => $stylist->name,
                'jobTitle' => $stylist->title ?? 'Hair Artist & Stylist',
                'image' => $stylist->photo_url ? url($stylist->photo_url) : null,
                'worksFor' => [
                    '@type' => 'HairSalon',
                    'name' => 'MORE Hair Studio Bandung',
                ],
            ];
        }

        return $schema;
    }
}
