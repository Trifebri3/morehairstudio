<?php

namespace App\Services\WhatsApp\Providers;

use App\Services\WhatsApp\Contracts\WhatsAppProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteWhatsAppProvider implements WhatsAppProvider
{
    protected string $token;

    public function __construct(array $config)
    {
        $this->token = $config['token'] ?? '';
    }

    public function sendText(string $to, string $message): mixed
    {
        return $this->sendRequest($to, [
            'message' => $message
        ]);
    }

    public function sendTemplate(string $to, string $template, array $parameters = []): mixed
    {
        // Fallback or format helper since Fonnte uses its own button/message syntax
        $message = "Template: {$template}\n";
        if (isset($parameters['body'])) {
            $message = $parameters['body'];
        }

        return $this->sendRequest($to, [
            'message' => $message
        ]);
    }

    public function sendMedia(string $to, string $mediaType, string $media): mixed
    {
        return $this->sendRequest($to, [
            'url' => $media,
            'message' => "Media Sent: {$mediaType}"
        ]);
    }

    public function sendInteractive(string $to, array $payload): mixed
    {
        // Map interactive buttons to Fonnte's standard buttons format
        return $this->sendRequest($to, [
            'message' => $payload['body']['text'] ?? 'Interactive Options',
            'buttons' => implode(',', array_column($payload['action']['buttons'] ?? [], 'reply'))
        ]);
    }

    /**
     * Send API HTTP request to Fonnte API.
     */
    protected function sendRequest(string $to, array $data): array
    {
        $cleanTo = preg_replace('/[^0-9]/', '', $to);

        if (empty($this->token)) {
            Log::info("Fonnte Simulation Outbound to {$cleanTo}", $data);
            return [
                'success' => true,
                'simulated' => true,
                'id' => 'simulated-fonnte-' . uniqid()
            ];
        }

        try {
            $payload = array_merge([
                'target' => $cleanTo,
                'countryCode' => '62'
            ], $data);

            $response = Http::withHeaders([
                'Authorization' => $this->token
            ])->post('https://api.fonnte.com/send', $payload);

            $result = $response->json();

            if ($response->successful() && ($result['status'] ?? false)) {
                return [
                    'success' => true,
                    'id' => $result['id'][0] ?? 'fonnte-' . uniqid()
                ];
            }

            Log::error("Fonnte API Error Outbound to {$cleanTo}: " . $response->body());
            return [
                'success' => false,
                'error' => $response->body(),
                'status' => $response->status()
            ];
        } catch (\Exception $e) {
            Log::error("Fonnte Provider Exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
