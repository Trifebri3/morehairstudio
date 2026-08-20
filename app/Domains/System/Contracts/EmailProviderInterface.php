<?php

namespace App\Domains\System\Contracts;

interface EmailProviderInterface
{
    /**
     * Send an email with optional attachments.
     */
    public function sendEmail(
        string $to,
        string $subject,
        string $body,
        ?string $attachmentPath = null,
        ?string $attachmentName = null
    ): bool;

    /**
     * Validate the email configuration.
     */
    public function validateConfiguration(array $config): bool;
}
