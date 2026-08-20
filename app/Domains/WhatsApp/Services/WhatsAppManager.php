<?php

namespace App\Domains\WhatsApp\Services;

use App\Domains\WhatsApp\Contracts\WhatsAppProviderInterface;
use App\Domains\WhatsApp\Providers\CloudApiWhatsAppProvider;
use App\Domains\WhatsApp\Providers\FonnteWhatsAppProvider;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsAppManager
{
    /**
     * Resolve the currently active WhatsApp provider.
     */
    public static function getActiveProvider(): WhatsAppProviderInterface
    {
        try {
            $activeConfig = DB::table('whatsapp_configurations')
                ->where('is_active', true)
                ->first();

            if ($activeConfig && !empty($activeConfig->config)) {
                $decryptedJson = Crypt::decryptString($activeConfig->config);
                $configData = json_decode($decryptedJson, true);

                if ($activeConfig->provider === 'cloud_api') {
                    return new CloudApiWhatsAppProvider($configData);
                } elseif ($activeConfig->provider === 'fonnte') {
                    if (empty($configData['token']) && env('FONNTE_TOKEN') && !app()->runningUnitTests()) {
                        $configData['token'] = env('FONNTE_TOKEN');
                    }
                    return new FonnteWhatsAppProvider($configData);
                }
            }

            // Fallback directly to .env environment variable if database is unconfigured (except in unit tests)
            if (env('FONNTE_TOKEN') && !app()->runningUnitTests()) {
                return new FonnteWhatsAppProvider([
                    'token' => env('FONNTE_TOKEN')
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed resolving active WhatsApp provider: " . $e->getMessage());
        }

        // Return a mock fallback provider so notifications don't crash
        return new CloudApiWhatsAppProvider(['mock' => true]);
    }
}
