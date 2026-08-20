<?php

namespace App\Domains\System\Services;

use App\Domains\WhatsApp\Services\WhatsAppManager;
use App\Domains\System\Models\EmailConfiguration;
use App\Domains\System\Models\EmailLog;
use App\Domains\System\Providers\SmtpEmailProvider;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommunicationService
{
    /**
     * Send plain text WhatsApp message.
     */
    public static function sendWhatsApp(string $to, string $text, ?int $bookingId = null): array
    {
        if (!self::isWhatsAppEnabled()) {
            Log::info("WhatsApp notifications globally disabled. Target: {$to}");
            return ['success' => false, 'error' => 'WhatsApp globally disabled'];
        }

        $provider = WhatsAppManager::getActiveProvider();
        return $provider->sendMessage($to, $text, $bookingId);
    }

    /**
     * Send pre-approved template WhatsApp message.
     */
    public static function sendWhatsAppTemplate(
        string $to,
        string $templateName,
        array $components = [],
        string $language = 'id',
        ?int $bookingId = null
    ): array {
        if (!self::isWhatsAppEnabled()) {
            Log::info("WhatsApp notifications globally disabled. Target: {$to}");
            return ['success' => false, 'error' => 'WhatsApp globally disabled'];
        }

        $provider = WhatsAppManager::getActiveProvider();
        return $provider->sendTemplate($to, $templateName, $components, $language, $bookingId);
    }

    /**
     * Send a PDF or document file via WhatsApp.
     */
    public static function sendWhatsAppDocument(string $to, string $documentUrl, string $fileName, ?int $bookingId = null): array
    {
        if (!self::isWhatsAppEnabled()) {
            Log::info("WhatsApp notifications globally disabled. Target: {$to}");
            return ['success' => false, 'error' => 'WhatsApp globally disabled'];
        }

        $provider = WhatsAppManager::getActiveProvider();
        return $provider->sendDocument($to, $documentUrl, $fileName, $bookingId);
    }

    /**
     * Send an image file via WhatsApp.
     */
    public static function sendWhatsAppImage(string $to, string $imageUrl, ?int $bookingId = null): array
    {
        if (!self::isWhatsAppEnabled()) {
            Log::info("WhatsApp notifications globally disabled. Target: {$to}");
            return ['success' => false, 'error' => 'WhatsApp globally disabled'];
        }

        $provider = WhatsAppManager::getActiveProvider();
        return $provider->sendImage($to, $imageUrl, $bookingId);
    }

    /**
     * Send SMTP email transaksional.
     */
    public static function sendEmail(
        string $to,
        string $subject,
        string $body,
        ?string $attachmentPath = null,
        ?string $attachmentName = null,
        ?int $bookingId = null,
        ?int $customerId = null
    ): bool {
        if (!self::isEmailEnabled()) {
            Log::info("Email notifications globally disabled. Target: {$to}");
            return false;
        }

        try {
            $emailConfig = EmailConfiguration::where('is_active', true)->first();

            if ($emailConfig) {
                // Decrypt password
                $password = !empty($emailConfig->password) ? Crypt::decryptString($emailConfig->password) : null;

                $provider = new SmtpEmailProvider([
                    'host' => $emailConfig->host,
                    'port' => $emailConfig->port,
                    'username' => $emailConfig->username,
                    'password' => $password,
                    'encryption' => $emailConfig->encryption,
                    'from_address' => $emailConfig->from_address,
                    'from_name' => $emailConfig->from_name,
                ]);

                $success = $provider->sendEmail($to, $subject, $body, $attachmentPath, $attachmentName);

                EmailLog::create([
                    'booking_id' => $bookingId,
                    'customer_id' => $customerId,
                    'recipient' => $to,
                    'subject' => $subject,
                    'status' => $success ? 'SENT' : 'FAILED',
                    'error_message' => $success ? null : 'Failed to send via SMTP transport'
                ]);

                return $success;
            }
        } catch (\Exception $e) {
            Log::error("SMTP Service error: " . $e->getMessage());
            EmailLog::create([
                'booking_id' => $bookingId,
                'customer_id' => $customerId,
                'recipient' => $to,
                'subject' => $subject,
                'status' => 'FAILED',
                'error_message' => $e->getMessage()
            ]);
        }

        return false;
    }

    /**
     * Check if WhatsApp channel is enabled.
     */
    public static function isWhatsAppEnabled(): bool
    {
        // Query settings dynamically or default to config
        $setting = DB::table('settings')->where('key', 'whatsapp_enabled')->first();
        if ($setting) {
            return filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
        }
        return config('communication.whatsapp_enabled', true);
    }

    /**
     * Check if Email channel is enabled.
     */
    public static function isEmailEnabled(): bool
    {
        $setting = DB::table('settings')->where('key', 'email_enabled')->first();
        if ($setting) {
            return filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
        }
        return config('communication.email_enabled', true);
    }
}
