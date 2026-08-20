<?php

namespace App\Domains\WhatsApp\Providers;

use App\Domains\WhatsApp\Contracts\WhatsAppProviderInterface;
use App\Domains\WhatsApp\Models\WhatsAppMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CloudApiWhatsAppProvider implements WhatsAppProviderInterface
{
    protected $token;
    protected $phoneNumberId;
    protected $version;
    protected $isMock = false;

    public function __construct(array $config)
    {
        $this->token = $config['token'] ?? null;
        $this->phoneNumberId = $config['phone_number_id'] ?? null;
        $this->version = $config['version'] ?? 'v20.0';
        $this->isMock = empty($this->token) || !empty($config['mock']);
    }

    public function sendMessage(string $to, string $text, ?int $bookingId = null): array
    {
        $cleanTo = preg_replace('/[^0-9]/', '', $to);

        $msgRecord = WhatsAppMessage::create([
            'booking_id' => $bookingId,
            'provider' => 'meta',
            'direction' => 'OUTBOUND',
            'message_type' => 'text',
            'recipient' => $cleanTo,
            'body' => $text,
            'status' => 'SENT',
            'sent_at' => Carbon::now()
        ]);

        if ($this->isMock) {
            Log::info("Meta WhatsApp Cloud API (MOCK) text to {$cleanTo}: {$text}");
            return ['success' => true, 'message_id' => 'mock_' . uniqid()];
        }

        try {
            $response = Http::withoutVerifying()
                ->withToken($this->token)
                ->post("https://graph.facebook.com/{$this->version}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $cleanTo,
                    'type' => 'text',
                    'text' => ['body' => $text]
                ]);

            $payload = $response->json();
            $success = $response->successful();
            $msgId = $payload['messages'][0]['id'] ?? null;

            $msgRecord->update([
                'external_message_id' => $msgId,
                'status' => $success ? 'SENT' : 'FAILED',
                'payload' => $payload,
                'error_message' => $success ? null : ($payload['error']['message'] ?? 'API Error')
            ]);

            return ['success' => $success, 'message_id' => $msgId, 'payload' => $payload];
        } catch (\Exception $e) {
            Log::error("Cloud API error: " . $e->getMessage());
            $msgRecord->update([
                'status' => 'FAILED',
                'error_message' => $e->getMessage()
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendTemplate(string $to, string $templateName, array $components = [], string $language = 'id', ?int $bookingId = null): array
    {
        $cleanTo = preg_replace('/[^0-9]/', '', $to);

        $msgRecord = WhatsAppMessage::create([
            'booking_id' => $bookingId,
            'provider' => 'meta',
            'direction' => 'OUTBOUND',
            'message_type' => 'template',
            'recipient' => $cleanTo,
            'template_name' => $templateName,
            'body' => "Template: {$templateName}",
            'status' => 'SENT',
            'sent_at' => Carbon::now()
        ]);

        if ($this->isMock) {
            Log::info("Meta WhatsApp Cloud API (MOCK) template {$templateName} to {$cleanTo}");
            return ['success' => true, 'message_id' => 'mock_' . uniqid()];
        }

        try {
            $payloadData = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $cleanTo,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => ['code' => $language]
                ]
            ];

            if (!empty($components)) {
                $payloadData['template']['components'] = $components;
            }

            $response = Http::withoutVerifying()
                ->withToken($this->token)
                ->post("https://graph.facebook.com/{$this->version}/{$this->phoneNumberId}/messages", $payloadData);

            $payload = $response->json();
            $success = $response->successful();
            $msgId = $payload['messages'][0]['id'] ?? null;

            $msgRecord->update([
                'external_message_id' => $msgId,
                'status' => $success ? 'SENT' : 'FAILED',
                'payload' => $payload,
                'error_message' => $success ? null : ($payload['error']['message'] ?? 'API Error')
            ]);

            return ['success' => $success, 'message_id' => $msgId, 'payload' => $payload];
        } catch (\Exception $e) {
            Log::error("Cloud API error: " . $e->getMessage());
            $msgRecord->update([
                'status' => 'FAILED',
                'error_message' => $e->getMessage()
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendMedia(string $to, string $mediaUrl, string $type, ?int $bookingId = null): array
    {
        $cleanTo = preg_replace('/[^0-9]/', '', $to);

        $msgRecord = WhatsAppMessage::create([
            'booking_id' => $bookingId,
            'provider' => 'meta',
            'direction' => 'OUTBOUND',
            'message_type' => 'media',
            'recipient' => $cleanTo,
            'body' => "Media: {$mediaUrl} (Type: {$type})",
            'status' => 'SENT',
            'sent_at' => Carbon::now()
        ]);

        if ($this->isMock) {
            Log::info("Meta WhatsApp Cloud API (MOCK) media to {$cleanTo}: {$mediaUrl}");
            return ['success' => true, 'message_id' => 'mock_' . uniqid()];
        }

        try {
            $response = Http::withoutVerifying()
                ->withToken($this->token)
                ->post("https://graph.facebook.com/{$this->version}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $cleanTo,
                    'type' => $type,
                    $type => ['link' => $mediaUrl]
                ]);

            $payload = $response->json();
            $success = $response->successful();
            $msgId = $payload['messages'][0]['id'] ?? null;

            $msgRecord->update([
                'external_message_id' => $msgId,
                'status' => $success ? 'SENT' : 'FAILED',
                'payload' => $payload,
                'error_message' => $success ? null : ($payload['error']['message'] ?? 'API Error')
            ]);

            return ['success' => $success, 'message_id' => $msgId, 'payload' => $payload];
        } catch (\Exception $e) {
            Log::error("Cloud API error: " . $e->getMessage());
            $msgRecord->update([
                'status' => 'FAILED',
                'error_message' => $e->getMessage()
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendDocument(string $to, string $documentUrl, string $fileName, ?int $bookingId = null): array
    {
        $cleanTo = preg_replace('/[^0-9]/', '', $to);

        $msgRecord = WhatsAppMessage::create([
            'booking_id' => $bookingId,
            'provider' => 'meta',
            'direction' => 'OUTBOUND',
            'message_type' => 'document',
            'recipient' => $cleanTo,
            'body' => "Document: {$fileName} at {$documentUrl}",
            'status' => 'SENT',
            'sent_at' => Carbon::now()
        ]);

        if ($this->isMock) {
            Log::info("Meta WhatsApp Cloud API (MOCK) document to {$cleanTo}: {$fileName}");
            return ['success' => true, 'message_id' => 'mock_' . uniqid()];
        }

        try {
            $response = Http::withoutVerifying()
                ->withToken($this->token)
                ->post("https://graph.facebook.com/{$this->version}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $cleanTo,
                    'type' => 'document',
                    'document' => [
                        'link' => $documentUrl,
                        'filename' => $fileName
                    ]
                ]);

            $payload = $response->json();
            $success = $response->successful();
            $msgId = $payload['messages'][0]['id'] ?? null;

            $msgRecord->update([
                'external_message_id' => $msgId,
                'status' => $success ? 'SENT' : 'FAILED',
                'payload' => $payload,
                'error_message' => $success ? null : ($payload['error']['message'] ?? 'API Error')
            ]);

            return ['success' => $success, 'message_id' => $msgId, 'payload' => $payload];
        } catch (\Exception $e) {
            Log::error("Cloud API error: " . $e->getMessage());
            $msgRecord->update([
                'status' => 'FAILED',
                'error_message' => $e->getMessage()
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendImage(string $to, string $imageUrl, ?int $bookingId = null): array
    {
        return $this->sendMedia($to, $imageUrl, 'image', $bookingId);
    }

    public function sendFile(string $to, string $fileUrl, ?int $bookingId = null): array
    {
        return $this->sendMedia($to, $fileUrl, 'document', $bookingId);
    }

    public function getStatus(string $messageId): string
    {
        if ($this->isMock) {
            return 'DELIVERED';
        }

        try {
            $response = Http::withoutVerifying()
                ->withToken($this->token)
                ->get("https://graph.facebook.com/{$this->version}/{$messageId}");
            
            if ($response->successful()) {
                $payload = $response->json();
                return strtoupper($payload['status'] ?? 'SENT');
            }
        } catch (\Exception $e) {
            Log::error("Cloud API Status retrieval error: " . $e->getMessage());
        }

        return 'SENT';
    }

    public function validateConfiguration(array $config): bool
    {
        if (empty($config['token']) || empty($config['phone_number_id'])) {
            return false;
        }

        if (!empty($config['mock'])) {
            return true;
        }

        try {
            $version = $config['version'] ?? 'v20.0';
            $response = Http::withoutVerifying()
                ->withToken($config['token'])
                ->get("https://graph.facebook.com/{$version}/{$config['phone_number_id']}");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
