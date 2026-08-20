<?php

namespace App\Domains\System\Providers;

use App\Domains\System\Contracts\EmailProviderInterface;
use App\Mail\GenericMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SmtpEmailProvider implements EmailProviderInterface
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function sendEmail(
        string $to,
        string $subject,
        string $body,
        ?string $attachmentPath = null,
        ?string $attachmentName = null
    ): bool {
        // Override SMTP settings at runtime
        $this->applyMailerConfig();

        try {
            Mail::mailer('smtp')
                ->to($to)
                ->send(new GenericMailable($subject, $body, $attachmentPath, $attachmentName));
            return true;
        } catch (\Exception $e) {
            Log::error("SMTP Email delivery failed: " . $e->getMessage());
            return false;
        }
    }

    public function validateConfiguration(array $config): bool
    {
        if (empty($config['host']) || empty($config['port']) || empty($config['username'])) {
            return false;
        }
        return true;
    }

    protected function applyMailerConfig(): void
    {
        config([
            'mail.mailers.smtp.host' => $this->config['host'] ?? config('mail.mailers.smtp.host'),
            'mail.mailers.smtp.port' => $this->config['port'] ?? config('mail.mailers.smtp.port'),
            'mail.mailers.smtp.username' => $this->config['username'] ?? config('mail.mailers.smtp.username'),
            'mail.mailers.smtp.password' => $this->config['password'] ?? config('mail.mailers.smtp.password'),
            'mail.mailers.smtp.encryption' => $this->config['encryption'] ?? config('mail.mailers.smtp.encryption'),
            'mail.from.address' => $this->config['from_address'] ?? config('mail.from.address'),
            'mail.from.name' => $this->config['from_name'] ?? config('mail.from.name'),
        ]);
    }
}
