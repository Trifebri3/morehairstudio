<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Mohapinkepane\WhatsAppCloud\Webhooks\WebhookRequestParser;
use App\Domains\WhatsApp\Models\WhatsAppMessage;
use App\Domains\WhatsApp\Models\WhatsAppWebhookEvent;
use Illuminate\Support\Facades\Log;

class ProcessWhatsAppWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $provider;
    protected string $eventId;

    public function __construct(string $provider, string $eventId)
    {
        $this->provider = $provider;
        $this->eventId = $eventId;
        $this->onConnection(config('whatsapp.queue.connection', 'database'));
        $this->onQueue(config('whatsapp.queue.queue', 'whatsapp'));
    }

    public function handle(WebhookRequestParser $parser): void
    {
        // 1. Retrieve the unprocessed webhook event
        $event = WhatsAppWebhookEvent::where('provider', $this->provider)
            ->where('event_id', $this->eventId)
            ->whereNull('processed_at')
            ->first();

        if (!$event) {
            return;
        }

        try {
            $payloadData = $event->payload;
            
            // 2. Parse payload using package WebhookRequestParser
            $parsedPayload = $parser->parse($payloadData);

            // 3. Process status updates
            foreach ($parsedPayload->statuses() as $statusUpdate) {
                if ($statusUpdate->id() !== $this->eventId) {
                    continue;
                }

                $status = strtoupper($statusUpdate->status());
                $timestamp = now(); // Meta timestamp can also be extracted if needed

                $msg = WhatsAppMessage::where('external_message_id', $this->eventId)->first();
                if ($msg) {
                    $updateData = ['status' => $status];
                    
                    if ($status === 'DELIVERED') {
                        $updateData['delivered_at'] = $timestamp;
                    } elseif ($status === 'READ') {
                        $updateData['read_at'] = $timestamp;
                    } elseif ($status === 'FAILED') {
                        $updateData['failed_at'] = $timestamp;
                        
                        $errors = $statusUpdate->errors();
                        if (!empty($errors)) {
                            $updateData['error_message'] = $errors[0]->message() ?? 'Meta API error';
                        }
                    }

                    $msg->update(array_merge($updateData, [
                        'payload' => array_merge($msg->payload ?? [], ['status_webhook_payload' => $statusUpdate->payload()])
                    ]));
                }
            }

            // 4. Process incoming messages
            foreach ($parsedPayload->messages() as $incomingMsg) {
                if ($incomingMsg->id() !== $this->eventId) {
                    continue;
                }

                $phone = $incomingMsg->sender();
                $type = $incomingMsg->type();
                $body = '';

                if ($type === 'text') {
                    $body = $incomingMsg->text() ?? '';
                } elseif ($type === 'interactive') {
                    $body = $incomingMsg->buttonReplyTitle() 
                        ?? $incomingMsg->listReplyTitle() 
                        ?? 'Interactive Option Selected';
                } elseif ($type === 'button') {
                    $body = $incomingMsg->extra('button')['text'] ?? 'Button Clicked';
                } else {
                    $body = "Incoming {$type} message";
                }

                // Log incoming message to DB
                WhatsAppMessage::create([
                    'provider' => $this->provider,
                    'direction' => 'INBOUND',
                    'message_type' => $type,
                    'recipient' => preg_replace('/[^0-9]/', '', $phone),
                    'external_message_id' => $incomingMsg->id(),
                    'body' => $body,
                    'status' => 'READ',
                    'payload' => ['incoming_webhook_payload' => $incomingMsg->payload()]
                ]);
            }

            // 5. Mark as processed
            $event->update([
                'processed_at' => now()
            ]);

        } catch (\Exception $e) {
            Log::error("ProcessWhatsAppWebhookJob exception for event {$this->eventId}: " . $e->getMessage());
            throw $e;
        }
    }
}
