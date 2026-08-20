<?php

namespace App\Services\WhatsApp\Contracts;

interface WhatsAppProvider
{
    public function sendText(string $to, string $message): mixed;
    
    public function sendTemplate(
        string $to,
        string $template,
        array $parameters = []
    ): mixed;
    
    public function sendMedia(
        string $to,
        string $mediaType,
        string $media
    ): mixed;
    
    public function sendInteractive(
        string $to,
        array $payload
    ): mixed;
}
