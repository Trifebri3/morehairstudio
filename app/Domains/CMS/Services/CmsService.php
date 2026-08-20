<?php

namespace App\Domains\CMS\Services;

use Illuminate\Support\Facades\DB;

class CmsService
{
    /**
     * Get translated content for a given key and locale.
     */
    public static function get(string $key, ?string $locale = null): string
    {
        $locale = $locale ?? session('locale', 'id'); // Default to Indonesian
        
        $record = DB::table('cms_contents')->where('key', $key)->first();
        
        if (!$record) {
            return self::getDefaultFallback($key, $locale);
        }

        $data = json_decode($record->value, true);
        
        if (!is_array($data)) {
            return $record->value ?? '';
        }

        return $data[$locale] ?? ($data['en'] ?? ($data['id'] ?? ''));
    }

    /**
     * Update translated values for a CMS key.
     */
    public static function set(string $key, array $translations): void
    {
        DB::table('cms_contents')->updateOrInsert(
            ['key' => $key],
            ['value' => json_encode($translations), 'updated_at' => now()]
        );
    }

    /**
     * Fallback values if DB is not seeded or record is missing
     */
    private static function getDefaultFallback(string $key, string $locale): string
    {
        $fallbacks = [
            'hero_tagline' => [
                'id' => 'Lebih Dari Sekadar Potong Rambut.',
                'en' => 'More Than A Haircut.'
            ],
            'hero_description' => [
                'id' => 'Pengalaman perawatan rambut modern yang dibangun berdasarkan gaya Anda, cerita Anda, dan momen Anda.',
                'en' => 'A modern grooming experience built around your style, your story, and your moment.'
            ],
            'about_tagline' => [
                'id' => 'Mendefinisikan Ulang Pengalaman Potong Rambut Anda',
                'en' => 'Re-Define Your Grooming Experience'
            ],
            'about_description_1' => [
                'id' => 'MORE Barber lahir untuk menghadirkan standar potong rambut pria ke level tertinggi. Kami memadukan keahlian teknik mencukur kelas dunia dengan kenyamanan modern, kopi premium, dan kemudahan booking digital berbasis web.',
                'en' => 'MORE Barber was born to bring the highest standards of men\'s grooming. We combine world-class barbering expertise with modern comfort, premium coffee, and web-based booking convenience.'
            ],
            'about_description_2' => [
                'id' => 'Setiap kunjungan dijamin presisi, nyaman, dan disesuaikan khusus untuk mengekspresikan jati diri Anda yang sesungguhnya.',
                'en' => 'Every visit is guaranteed to be precise, comfortable, and tailored specifically to express your true self.'
            ],
            'why_title' => [
                'id' => 'Estetika Maksimal',
                'en' => 'Maximum Aesthetics'
            ],
            'why_subtitle' => [
                'id' => 'Komitmen kenyamanan dan ketepatan potongan di setiap sudut outlet kami.',
                'en' => 'Commitment to comfort and precision cuts at every corner of our outlets.'
            ]
        ];

        return $fallbacks[$key][$locale] ?? $fallbacks[$key]['id'] ?? '';
    }
}
