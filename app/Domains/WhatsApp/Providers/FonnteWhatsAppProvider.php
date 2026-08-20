<?php

namespace App\Domains\WhatsApp\Providers;

use App\Domains\WhatsApp\Contracts\WhatsAppProviderInterface;
use App\Domains\WhatsApp\Models\WhatsAppMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FonnteWhatsAppProvider implements WhatsAppProviderInterface
{
    protected $token;
    protected $isMock = false;

    public function __construct(array $config)
    {
        $this->token = $config['token'] ?? null;
        $this->isMock = empty($this->token) || !empty($config['mock']);
    }

    public function sendMessage(string $to, string $text, ?int $bookingId = null): array
    {
        $cleanTo = preg_replace('/[^0-9]/', '', $to);

        $msgRecord = WhatsAppMessage::create([
            'booking_id' => $bookingId,
            'provider' => 'fonnte',
            'direction' => 'OUTBOUND',
            'message_type' => 'text',
            'recipient' => $cleanTo,
            'body' => $text,
            'status' => 'SENT',
            'sent_at' => Carbon::now()
        ]);

        if ($this->isMock) {
            Log::info("Fonnte WhatsApp API (MOCK) text to {$cleanTo}: {$text}");
            return ['success' => true, 'message_id' => 'mock_fn_' . uniqid()];
        }

        try {
            $response = Http::withoutVerifying()
                ->withHeaders(['Authorization' => $this->token])
                ->post('https://api.fonnte.com/send', [
                    'target' => $cleanTo,
                    'message' => $text
                ]);

            $payload = $response->json();
            $success = $response->successful() && ($payload['status'] ?? false);
            $msgId = $payload['id'][0] ?? null;

            $msgRecord->update([
                'external_message_id' => $msgId,
                'status' => $success ? 'SENT' : 'FAILED',
                'payload' => $payload,
                'error_message' => $success ? null : ($payload['reason'] ?? 'API Error')
            ]);

            return ['success' => $success, 'message_id' => $msgId, 'payload' => $payload];
        } catch (\Exception $e) {
            Log::error("Fonnte API error: " . $e->getMessage());
            $msgRecord->update([
                'status' => 'FAILED',
                'error_message' => $e->getMessage()
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendTemplate(string $to, string $templateName, array $components = [], string $language = 'id', ?int $bookingId = null): array
    {
        // Fonnte does not require pre-registered Meta templates, it maps variables in body
        // We simulate template parsing or just send general formatted message text
        $cleanTo = preg_replace('/[^0-9]/', '', $to);
        
        // Convert components to clean text body if present
        $body = "Template: {$templateName}";
        if (!empty($components)) {
            $body .= " " . json_encode($components);
        }

        $msgRecord = WhatsAppMessage::create([
            'booking_id' => $bookingId,
            'provider' => 'fonnte',
            'direction' => 'OUTBOUND',
            'message_type' => 'template',
            'recipient' => $cleanTo,
            'template_name' => $templateName,
            'body' => $body,
            'status' => 'SENT',
            'sent_at' => Carbon::now()
        ]);

        if ($this->isMock) {
            Log::info("Fonnte WhatsApp API (MOCK) template {$templateName} to {$cleanTo}");
            return ['success' => true, 'message_id' => 'mock_fn_' . uniqid()];
        }

        try {
            $response = Http::withoutVerifying()
                ->withHeaders(['Authorization' => $this->token])
                ->post('https://api.fonnte.com/send', [
                    'target' => $cleanTo,
                    'message' => $body
                ]);

            $payload = $response->json();
            $success = $response->successful() && ($payload['status'] ?? false);
            $msgId = $payload['id'][0] ?? null;

            $msgRecord->update([
                'external_message_id' => $msgId,
                'status' => $success ? 'SENT' : 'FAILED',
                'payload' => $payload,
                'error_message' => $success ? null : ($payload['reason'] ?? 'API Error')
            ]);

            return ['success' => $success, 'message_id' => $msgId, 'payload' => $payload];
        } catch (\Exception $e) {
            Log::error("Fonnte API error: " . $e->getMessage());
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
            'provider' => 'fonnte',
            'direction' => 'OUTBOUND',
            'message_type' => 'media',
            'recipient' => $cleanTo,
            'body' => "Media: {$mediaUrl} (Type: {$type})",
            'status' => 'SENT',
            'sent_at' => Carbon::now()
        ]);

        if ($this->isMock) {
            Log::info("Fonnte WhatsApp API (MOCK) media to {$cleanTo}: {$mediaUrl}");
            return ['success' => true, 'message_id' => 'mock_fn_' . uniqid()];
        }

        try {
            $localFile = $this->getLocalPath($mediaUrl);

            if ($localFile) {
                $response = Http::withoutVerifying()
                    ->withHeaders(['Authorization' => $this->token])
                    ->attach('file', file_get_contents($localFile), basename($localFile))
                    ->post('https://api.fonnte.com/send', [
                        'target' => $cleanTo,
                        'message' => "Media file"
                    ]);
            } else {
                $response = Http::withoutVerifying()
                    ->withHeaders(['Authorization' => $this->token])
                    ->post('https://api.fonnte.com/send', [
                        'target' => $cleanTo,
                        'url' => $mediaUrl,
                        'message' => "Media file"
                    ]);
            }

            $payload = $response->json();
            $success = $response->successful() && ($payload['status'] ?? false);
            $msgId = $payload['id'][0] ?? null;

            $msgRecord->update([
                'external_message_id' => $msgId,
                'status' => $success ? 'SENT' : 'FAILED',
                'payload' => $payload,
                'error_message' => $success ? null : ($payload['reason'] ?? 'API Error')
            ]);

            return ['success' => $success, 'message_id' => $msgId, 'payload' => $payload];
        } catch (\Exception $e) {
            Log::error("Fonnte API error: " . $e->getMessage());
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
            'provider' => 'fonnte',
            'direction' => 'OUTBOUND',
            'message_type' => 'document',
            'recipient' => $cleanTo,
            'body' => "Document: {$fileName} at {$documentUrl}",
            'status' => 'SENT',
            'sent_at' => Carbon::now()
        ]);

        if ($this->isMock) {
            Log::info("Fonnte WhatsApp API (MOCK) document to {$cleanTo}: {$fileName}");
            return ['success' => true, 'message_id' => 'mock_fn_' . uniqid()];
        }

        try {
            $localFile = $this->getLocalPath($documentUrl);

            if ($localFile) {
                $response = Http::withoutVerifying()
                    ->withHeaders(['Authorization' => $this->token])
                    ->attach('file', file_get_contents($localFile), $fileName ?: basename($localFile))
                    ->post('https://api.fonnte.com/send', [
                        'target' => $cleanTo,
                        'message' => "Dokumen lampiran"
                    ]);
            } else {
                $response = Http::withoutVerifying()
                    ->withHeaders(['Authorization' => $this->token])
                    ->post('https://api.fonnte.com/send', [
                        'target' => $cleanTo,
                        'url' => $documentUrl,
                        'filename' => $fileName,
                        'message' => "Dokumen lampiran"
                    ]);
            }

            $payload = $response->json();
            $success = $response->successful() && ($payload['status'] ?? false);
            $msgId = $payload['id'][0] ?? null;

            $msgRecord->update([
                'external_message_id' => $msgId,
                'status' => $success ? 'SENT' : 'FAILED',
                'payload' => $payload,
                'error_message' => $success ? null : ($payload['reason'] ?? 'API Error')
            ]);

            return ['success' => $success, 'message_id' => $msgId, 'payload' => $payload];
        } catch (\Exception $e) {
            Log::error("Fonnte API error: " . $e->getMessage());
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
        return $this->sendMedia($to, $fileUrl, 'file', $bookingId);
    }

    public function getStatus(string $messageId): string
    {
        if ($this->isMock) {
            return 'DELIVERED';
        }

        // Fonnte message status API checks
        // Usually checked in incoming webhooks, but status check is supported
        return 'SENT';
    }

    public function validateConfiguration(array $config): bool
    {
        if (empty($config['token'])) {
            return false;
        }

        if (!empty($config['mock'])) {
            return true;
        }

        try {
            $response = Http::withoutVerifying()
                ->withHeaders(['Authorization' => $config['token']])
                ->post('https://api.fonnte.com/device');
            
            return $response->successful() && $response->json('status') === true;
        } catch (\Exception $e) {
            return false;
        }
    }
    protected function getLocalPath(string $url): ?string
    {
        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '';

        if (str_starts_with($path, '/storage/')) {
            $localPath = public_path($path);
            if (file_exists($localPath)) {
                return $localPath;
            }
        }
        
        $directPath = public_path(ltrim($path, '/'));
        if (file_exists($directPath)) {
            return $directPath;
        }

        return null;
    }
}
