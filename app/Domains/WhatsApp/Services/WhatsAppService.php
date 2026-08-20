<?php

namespace App\Domains\WhatsApp\Services;

class WhatsAppService
{
    /**
     * Forward to core production Service Layer.
     */
    public static function sendMessage(string $target, string $message, ?int $bookingId = null): bool
    {
        return \App\Services\WhatsAppService::sendTextMessage($target, $message, $bookingId);
    }
}
