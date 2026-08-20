<?php

namespace App\Domains\WhatsApp\Contracts;

interface WhatsAppProviderInterface
{
    /**
     * Send a plain text message.
     */
    public function sendMessage(string $to, string $text, ?int $bookingId = null): array;

    /**
     * Send a template message with dynamic variables.
     */
    public function sendTemplate(string $to, string $templateName, array $components = [], string $language = 'id', ?int $bookingId = null): array;

    /**
     * Send generic media (image, audio, video, document).
     */
    public function sendMedia(string $to, string $mediaUrl, string $type, ?int $bookingId = null): array;

    /**
     * Send a PDF or document with custom filename.
     */
    public function sendDocument(string $to, string $documentUrl, string $fileName, ?int $bookingId = null): array;

    /**
     * Send a simple image.
     */
    public function sendImage(string $to, string $imageUrl, ?int $bookingId = null): array;

    /**
     * Send a generic file.
     */
    public function sendFile(string $to, string $fileUrl, ?int $bookingId = null): array;

    /**
     * Get the delivery status of a sent message.
     */
    public function getStatus(string $messageId): string;

    /**
     * Test/validate connection configuration.
     */
    public function validateConfiguration(array $config): bool;
}
