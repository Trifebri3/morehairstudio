<?php

namespace App\Services\WhatsApp;

use App\Services\WhatsApp\Contracts\WhatsAppProvider;
use App\Services\WhatsApp\Providers\MetaWhatsAppProvider;
use App\Services\WhatsApp\Providers\FonnteWhatsAppProvider;
use App\Domains\WhatsApp\Models\WhatsAppMessage;
use Illuminate\Support\Facades\Log;

class WhatsAppManager
{
    protected WhatsAppProvider $driver;
    protected string $providerName;

    public function __construct()
    {
        $this->providerName = config('whatsapp.provider', 'meta');
        $this->driver = $this->resolveDriver($this->providerName);
    }

    /**
     * Resolve the active provider driver instance.
     */
    protected function resolveDriver(string $provider): WhatsAppProvider
    {
        return match ($provider) {
            'fonnte' => new FonnteWhatsAppProvider(config('whatsapp.fonnte', [])),
            default => new MetaWhatsAppProvider(config('whatsapp.meta', [])),
        };
    }

    /**
     * Send a plain outbound text message and log it in the database.
     */
    public function sendText(string $to, string $message, ?int $bookingId = null, ?int $customerId = null): mixed
    {
        $msgRecord = $this->createMessageRecord($to, 'text', $message, null, $bookingId, $customerId);

        $result = $this->driver->sendText($to, $message);

        $this->updateMessageRecord($msgRecord, $result);

        return $result;
    }

    /**
     * Send a template message and log it in the database.
     */
    public function sendTemplate(
        string $to,
        string $template,
        array $parameters = [],
        ?int $bookingId = null,
        ?int $customerId = null
    ): mixed {
        $msgRecord = $this->createMessageRecord($to, 'template', "Template: {$template}", $template, $bookingId, $customerId);

        $result = $this->driver->sendTemplate($to, $template, $parameters);

        $this->updateMessageRecord($msgRecord, $result);

        return $result;
    }

    /**
     * Send media attachments.
     */
    public function sendMedia(string $to, string $mediaType, string $media, ?int $bookingId = null, ?int $customerId = null): mixed
    {
        $msgRecord = $this->createMessageRecord($to, 'media', "Media: {$mediaType}", null, $bookingId, $customerId);

        $result = $this->driver->sendMedia($to, $mediaType, $media);

        $this->updateMessageRecord($msgRecord, $result);

        return $result;
    }

    /**
     * Send interactive button elements.
     */
    public function sendInteractive(string $to, array $payload, ?int $bookingId = null, ?int $customerId = null): mixed
    {
        $msgRecord = $this->createMessageRecord($to, 'interactive', "Interactive message payload", null, $bookingId, $customerId);

        $result = $this->driver->sendInteractive($to, $payload);

        $this->updateMessageRecord($msgRecord, $result);

        return $result;
    }

    /**
     * Initialize DB record tracking.
     */
    protected function createMessageRecord(
        string $to, 
        string $type, 
        string $body, 
        ?string $templateName = null,
        ?int $bookingId = null,
        ?int $customerId = null
    ): WhatsAppMessage {
        return WhatsAppMessage::create([
            'booking_id' => $bookingId,
            'customer_id' => $customerId,
            'provider' => $this->providerName,
            'direction' => 'OUTBOUND',
            'message_type' => $type,
            'recipient' => preg_replace('/[^0-9]/', '', $to),
            'template_name' => $templateName,
            'body' => $body,
            'status' => 'QUEUED',
            'payload' => ['sending_payload' => ['to' => $to, 'type' => $type]]
        ]);
    }

    /**
     * Finalize DB status logging post-sending.
     */
    protected function updateMessageRecord(WhatsAppMessage $msgRecord, array $result)
    {
        $success = $result['success'] ?? false;
        
        $msgRecord->update([
            'external_message_id' => $result['messages'][0]['id'] ?? $result['id'] ?? null,
            'status' => $success ? 'SENT' : 'FAILED',
            'sent_at' => $success ? now() : null,
            'failed_at' => !$success ? now() : null,
            'error_message' => !$success ? ($result['error'] ?? 'API delivery failure') : null,
            'payload' => array_merge($msgRecord->payload ?? [], ['response_payload' => $result])
        ]);
    }
}
