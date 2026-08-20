<?php

namespace App\Notifications\Channels;

use App\Services\WhatsApp\WhatsAppManager;
use Illuminate\Notifications\Notification;

class WhatsAppChannel
{
    protected WhatsAppManager $whatsapp;

    public function __construct(WhatsAppManager $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification): mixed
    {
        $messageData = $notification->toWhatsApp($notifiable);

        if (empty($messageData['to'])) {
            return null;
        }

        $to = $messageData['to'];
        $bookingId = $messageData['booking_id'] ?? null;
        $customerId = $messageData['customer_id'] ?? null;
        $provider = config('whatsapp.provider', 'meta');

        if ($provider === 'meta' && isset($messageData['template'])) {
            return $this->whatsapp->sendTemplate(
                $to,
                $messageData['template'],
                $messageData['parameters'] ?? [],
                $bookingId,
                $customerId
            );
        }

        if (isset($messageData['message'])) {
            return $this->whatsapp->sendText(
                $to,
                $messageData['message'],
                $bookingId,
                $customerId
            );
        }

        if (isset($messageData['template'])) {
            return $this->whatsapp->sendTemplate(
                $to,
                $messageData['template'],
                $messageData['parameters'] ?? [],
                $bookingId,
                $customerId
            );
        }

        return null;
    }
}
