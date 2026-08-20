<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Domains\WhatsApp\Models\WhatsAppMessage;

class WhatsAppService
{
    /**
     * Send a free-form WhatsApp text message via Meta Cloud API.
     */
    public static function sendTextMessage(string $to, string $text, ?int $bookingId = null): bool
    {
        $config = config('services.whatsapp');
        $token = $config['token'];
        $phoneNumberId = $config['phone_number_id'];
        $version = $config['version'] ?? 'v20.0';

        $cleanTo = preg_replace('/[^0-9]/', '', $to);

        // Pre-create pending record in database
        $msgRecord = WhatsAppMessage::create([
            'booking_id' => $bookingId,
            'phone' => $cleanTo,
            'type' => 'text',
            'body' => $text,
            'status' => $token ? 'pending' : 'sent',
            'response_payload' => ['simulated' => !$token]
        ]);

        if (!$token || !$phoneNumberId) {
            logger()->info("WhatsApp Simulation (Text) to {$cleanTo}: {$text}");
            return true;
        }

        try {
            $response = Http::withToken($token)
                ->post("https://graph.facebook.com/{$version}/{$phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $cleanTo,
                    'type' => 'text',
                    'text' => [
                        'body' => $text
                    ]
                ]);

            $payload = $response->json();
            $success = $response->successful();
            $messageId = $payload['messages'][0]['id'] ?? null;

            $msgRecord->update([
                'message_id' => $messageId,
                'status' => $success ? 'sent' : 'failed',
                'response_payload' => $payload
            ]);

            return $success;
        } catch (\Exception $e) {
            logger()->error("WhatsApp Service Error (Text): " . $e->getMessage());
            $msgRecord->update([
                'status' => 'failed',
                'response_payload' => ['error' => $e->getMessage()]
            ]);
            return false;
        }
    }

    /**
     * Send a pre-approved WhatsApp interactive template message.
     */
    public static function sendTemplateMessage(
        string $to, 
        string $templateName, 
        array $components = [], 
        string $language = 'id', 
        ?int $bookingId = null
    ): bool {
        $config = config('services.whatsapp');
        $token = $config['token'];
        $phoneNumberId = $config['phone_number_id'];
        $version = $config['version'] ?? 'v20.0';

        $cleanTo = preg_replace('/[^0-9]/', '', $to);

        $msgRecord = WhatsAppMessage::create([
            'booking_id' => $bookingId,
            'phone' => $cleanTo,
            'type' => 'template',
            'template_name' => $templateName,
            'body' => "Template: {$templateName} (Language: {$language})",
            'status' => $token ? 'pending' : 'sent',
            'response_payload' => ['simulated' => !$token]
        ]);

        if (!$token || !$phoneNumberId) {
            logger()->info("WhatsApp Simulation (Template: {$templateName}) to {$cleanTo}");
            return true;
        }

        try {
            $payloadData = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $cleanTo,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => $language
                    ]
                ]
            ];

            if (!empty($components)) {
                $payloadData['template']['components'] = $components;
            }

            $response = Http::withToken($token)
                ->post("https://graph.facebook.com/{$version}/{$phoneNumberId}/messages", $payloadData);

            $payload = $response->json();
            $success = $response->successful();
            $messageId = $payload['messages'][0]['id'] ?? null;

            $msgRecord->update([
                'message_id' => $messageId,
                'status' => $success ? 'sent' : 'failed',
                'response_payload' => $payload
            ]);

            return $success;
        } catch (\Exception $e) {
            logger()->error("WhatsApp Service Error (Template): " . $e->getMessage());
            $msgRecord->update([
                'status' => 'failed',
                'response_payload' => ['error' => $e->getMessage()]
            ]);
            return false;
        }
    }
}
