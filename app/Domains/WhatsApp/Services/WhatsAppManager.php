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

            $configData = [];
            if ($activeConfig && !empty($activeConfig->config)) {
                try {
                    $decryptedJson = Crypt::decryptString($activeConfig->config);
                    $configData = json_decode($decryptedJson, true) ?: [];
                } catch (\Exception $decryptEx) {
                    Log::warning("Could not decrypt whatsapp config from DB: " . $decryptEx->getMessage());
                }

                if ($activeConfig->provider === 'cloud_api') {
                    if (!empty($configData['token'])) {
                        return new CloudApiWhatsAppProvider($configData);
                    }
                } elseif ($activeConfig->provider === 'fonnte') {
                    $token = $configData['token'] ?? env('FONNTE_TOKEN');
                    if (!empty($token)) {
                        return new FonnteWhatsAppProvider([
                            'token' => $token,
                            'mock' => false
                        ]);
                    }
                }
            }

            // Fallback directly to .env environment variable
            $envToken = env('FONNTE_TOKEN');
            if (!empty($envToken) && !app()->runningUnitTests()) {
                return new FonnteWhatsAppProvider([
                    'token' => $envToken,
                    'mock' => false
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed resolving active WhatsApp provider: " . $e->getMessage());
        }

        // Return a mock fallback provider so notifications don't crash
        return new CloudApiWhatsAppProvider(['mock' => true]);
    }
}
