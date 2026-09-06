<?php

namespace App\Domains\System\Listeners;

use App\Domains\Booking\Events\BookingCreated;
use App\Domains\Booking\Events\BookingConfirmed;
use App\Domains\Booking\Events\BookingCancelled;
use App\Domains\Booking\Events\BookingRescheduled;
use App\Domains\Booking\Events\BookingCheckedIn;
use App\Domains\Booking\Events\BookingExpired;
use App\Domains\Booking\Events\BookingCompleted;

use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Services\TicketGeneratorService;
use App\Domains\System\Services\CommunicationService;
use App\Domains\WhatsApp\Models\WhatsAppTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommunicationListener
{
    public function handleBookingCreated(BookingCreated $event): void
    {
        $booking = $event->booking;

        // Generate digital ticket (idempotent PDF & QR) so it is ready
        $ticket = TicketGeneratorService::generateForBooking($booking);

        $this->processAutomations('BOOKING_CREATED', $booking, $ticket);
        $this->notifyStylist($booking);

        // Deliver QR Code image & PDF ticket immediately on booking creation if not handled by automation
        if (CommunicationService::isWhatsAppEnabled() && $booking->customer) {
            $hasQrAutomation = DB::table('whatsapp_automations')
                ->where('event_type', 'BOOKING_CREATED')
                ->where('is_active', true)
                ->where('include_qr', true)
                ->exists();

            if (!$hasQrAutomation) {
                $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($booking->booking_code);
                CommunicationService::sendWhatsAppImage(
                    $booking->customer->phone,
                    $qrUrl,
                    $booking->id,
                    "QR Code Check-In Reservasi {$booking->booking_code} - MORE Hair Studio"
                );
            }
        }
    }

    public function handleBookingConfirmed(BookingConfirmed $event): void
    {
        $booking = $event->booking;

        // 1. Generate digital ticket (idempotent PDF & QR)
        $ticket = TicketGeneratorService::generateForBooking($booking);

        // 2. Trigger automations for BOOKING_CONFIRMED
        $this->processAutomations('BOOKING_CONFIRMED', $booking, $ticket);

        // Notify stylist
        $this->notifyStylist($booking);

        // 3. Deliver QR Code if not already dispatched by automations
        if (CommunicationService::isWhatsAppEnabled() && $booking->customer) {
            $hasQrAutomation = DB::table('whatsapp_automations')
                ->where('event_type', 'BOOKING_CONFIRMED')
                ->where('is_active', true)
                ->where('include_qr', true)
                ->exists();

            if (!$hasQrAutomation) {
                $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($booking->booking_code);
                CommunicationService::sendWhatsAppImage(
                    $booking->customer->phone,
                    $qrUrl,
                    $booking->id,
                    "QR Code Check-In Reservasi {$booking->booking_code} - MORE Hair Studio"
                );
            }
        }

        if (CommunicationService::isEmailEnabled() && !empty($booking->customer->email)) {
            $bookingDate = $booking->booking_date->format('d M Y');
            $subject = "Konfirmasi Tiket & QR Reservasi {$booking->booking_code} - MORE Hair Studio";
            $qrUrl = !empty($ticket->qr_code_path) ? asset($ticket->qr_code_path) : "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($booking->booking_code);
            $ticketUrl = url("/booking/ticket/{$booking->booking_code}");

            $emailBody = "
                <div style='font-family: Arial, sans-serif; padding: 25px; color: #222; max-width: 580px; margin: 0 auto; border: 1px solid #eaeaea; border-radius: 16px; background: #ffffff;'>
                    <div style='text-align: center; margin-bottom: 20px;'>
                        <img src='https://defineyoumore.com/logo/logo.png' alt='MORE Hair Studio' style='height: 38px; margin: 0 auto 8px auto; display: block;' />
                        <p style='color: #888; font-size: 11px; text-transform: uppercase; margin-top: 4px;'>Urban Barbershop & Creative Ecosystem</p>
                    </div>

                    <p>Halo <strong>{$booking->customer->name}</strong>,</p>
                    <p>Pemesanan reservasi Anda telah berhasil dikonfirmasi. Berikut adalah Tiket & QR Code resmi Anda:</p>

                    <div style='text-align: center; margin: 25px 0; padding: 20px; background-color: #fafaf9; border-radius: 12px; border: 1px solid #ebebeb;'>
                        <img src='{$qrUrl}' alt='QR Code {$booking->booking_code}' style='width: 180px; height: 180px; display: inline-block; margin-bottom: 12px;' />
                        <div style='font-family: monospace; font-size: 18px; font-weight: bold; color: #111; letter-spacing: 1px;'>{$booking->booking_code}</div>
                    </div>

                    <table style='width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 13px;'>
                        <tr style='border-bottom: 1px solid #f0f0f0;'><td style='padding: 8px 0; color: #666;'>Studio Lounge:</td><td style='padding: 8px 0; font-weight: bold; text-align: right;'>{$booking->outlet->name}</td></tr>
                        <tr style='border-bottom: 1px solid #f0f0f0;'><td style='padding: 8px 0; color: #666;'>Tanggal & Waktu:</td><td style='padding: 8px 0; font-weight: bold; text-align: right;'>{$bookingDate}</td></tr>
                        <tr style='border-bottom: 1px solid #f0f0f0;'><td style='padding: 8px 0; color: #666;'>Hair Artist:</td><td style='padding: 8px 0; font-weight: bold; text-align: right;'>" . ($booking->stylist->name ?? '-') . "</td></tr>
                    </table>

                    <div style='text-align: center; margin: 25px 0;'>
                        <a href='{$ticketUrl}' style='display: inline-block; padding: 12px 24px; background-color: #c9512d; color: #ffffff; text-decoration: none; font-weight: bold; font-size: 12px; text-transform: uppercase; border-radius: 8px;'>Lihat E-Ticket Digital</a>
                    </div>

                    <p style='font-size: 11px; color: #999; text-align: center; margin-top: 20px; border-top: 1px solid #f0f0f0; padding-top: 15px;'>Tunjukkan QR Code ini di scanner tablet studio MORE atau sebutkan Kode Booking saat kedatangan.</p>
                </div>
            ";

            $attachPath = null;
            if (!empty($ticket->pdf_path)) {
                $checkPath = public_path($ticket->pdf_path);
                if (file_exists($checkPath)) {
                    $attachPath = $checkPath;
                } else {
                    $altPath = storage_path('app/public/' . str_replace('/storage/', '', $ticket->pdf_path));
                    if (file_exists($altPath)) {
                        $attachPath = $altPath;
                    }
                }
            }

            CommunicationService::sendEmail(
                $booking->customer->email,
                $subject,
                $emailBody,
                $attachPath,
                "Ticket-{$booking->booking_code}.pdf",
                $booking->id,
                $booking->customer_id
            );
        }
    }

    public function handleBookingCancelled(BookingCancelled $event): void
    {
        $this->processAutomations('BOOKING_CANCELLED', $event->booking);
    }

    public function handleBookingRescheduled(BookingRescheduled $event): void
    {
        $this->processAutomations('BOOKING_RESCHEDULED', $event->booking);
    }

    public function handleBookingCheckedIn(BookingCheckedIn $event): void
    {
        $this->processAutomations('BOOKING_CHECKED_IN', $event->booking);
    }

    public function handleBookingExpired(BookingExpired $event): void
    {
        $this->processAutomations('BOOKING_EXPIRED', $event->booking);
    }

    public function handleBookingCompleted(BookingCompleted $event): void
    {
        $this->processAutomations('BOOKING_COMPLETED', $event->booking);
    }

    /**
     * Notify stylist on booking event.
     */
    protected function notifyStylist(Booking $booking): void
    {
        try {
            $stylist = $booking->stylist;
            if ($stylist && !empty($stylist->phone)) {
                $customerName = $booking->customer->name ?? 'Guest';
                $bookingDate = $booking->booking_date->format('d M Y');
                $items = $booking->items()->with('service')->get();
                $firstItem = $items->first();
                $bookingTime = $firstItem ? substr($firstItem->start_time, 0, 5) : 'Sesuai Jadwal';
                $serviceNames = $items->map(fn($i) => $i->service->name)->implode(', ');

                $msg = "Halo {$stylist->name}, Anda memiliki reservasi baru:\n";
                $msg .= "Kode Booking: {$booking->booking_code}\n";
                $msg .= "Pelanggan: {$customerName}\n";
                $msg .= "Layanan: {$serviceNames}\n";
                $msg .= "Tanggal: {$bookingDate}\n";
                $msg .= "Pukul: {$bookingTime}\n\n";
                $msg .= "Mohon bersiap-siap melayani pelanggan.";

                CommunicationService::sendWhatsApp($stylist->phone, $msg, $booking->id);
            }
        } catch (\Exception $e) {
            Log::error("Failed to notify stylist: " . $e->getMessage());
        }
    }

    /**
     * Process active automations mapped to event.
     */
    protected function processAutomations(string $eventType, Booking $booking, $ticket = null): void
    {
        try {
            $automations = DB::table('whatsapp_automations')
                ->where('event_type', $eventType)
                ->where('is_active', true)
                ->get();

            foreach ($automations as $auto) {
                // Scope to outlet
                if (!empty($auto->outlet_id) && $auto->outlet_id != $booking->outlet_id) {
                    continue;
                }

                // Determine recipient phone
                $phone = null;
                if ($auto->recipient === 'stylist') {
                    $phone = $booking->stylist->phone ?? null;
                } else {
                    $phone = $booking->customer->phone ?? null;
                }

                if (!$phone) {
                    continue;
                }

                // Load template
                $template = DB::table('whatsapp_templates')
                    ->where('template_name', $auto->template_name)
                    ->where('is_active', true)
                    ->first();

                if ($template) {
                    $bodyText = $this->resolveMessageVariables($template->body, $booking, $ticket);
                    
                    // If automation has include_qr enabled, send QR code image with caption
                    if (!empty($auto->include_qr)) {
                        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($booking->booking_code);
                        $imgRes = CommunicationService::sendWhatsAppImage($phone, $qrUrl, $booking->id, $bodyText);

                        // If image sending failed, fallback to plain text message
                        if (!($imgRes['success'] ?? false)) {
                            CommunicationService::sendWhatsApp($phone, $bodyText, $booking->id);
                        }
                    } else {
                        // Dispatch text message
                        CommunicationService::sendWhatsApp($phone, $bodyText, $booking->id);
                    }

                    // Dispatch attachment file if configured in template
                    if (!empty($template->file_path)) {
                        $fileUrl = asset($template->file_path);
                        CommunicationService::sendWhatsAppDocument(
                            $phone,
                            $fileUrl,
                            basename($template->file_path),
                            $booking->id
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Error processing automations for {$eventType}: " . $e->getMessage());
        }
    }

    /**
     * Resolve placeholder template bindings.
     */
    protected function resolveMessageVariables(string $text, Booking $booking, $ticket = null): string
    {
        $customerName = $booking->customer->name ?? 'Guest';
        $bookingDate = $booking->booking_date->format('d M Y');
        $items = $booking->items()->with('service')->get();
        $firstItem = $items->first();
        $bookingTime = $firstItem ? substr($firstItem->start_time, 0, 5) : 'Sesuai Jadwal';
        $serviceNames = $items->map(fn($i) => $i->service->name)->implode(', ');
        $outletName = $booking->outlet->name ?? 'More Hair Studio';
        $barberName = $booking->stylist->name ?? 'Barber';
        $bookingCode = $booking->booking_code;
        $ticketUrl = url("/booking/ticket/{$bookingCode}");
        $passcode = $ticket->passcode ?? '';

        $replacements = [
            '{{customer_name}}' => $customerName,
            '{{booking_date}}' => $bookingDate,
            '{{booking_time}}' => $bookingTime,
            '{{outlet_name}}' => $outletName,
            '{{barber_name}}' => $barberName,
            '{{service_name}}' => $serviceNames,
            '{{booking_code}}' => $bookingCode,
            '{{ticket_url}}' => $ticketUrl,
            '{{passcode}}' => $passcode,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    /**
     * Subscribe map registration.
     */
    public function subscribe($events): array
    {
        return [
            BookingCreated::class => 'handleBookingCreated',
            BookingConfirmed::class => 'handleBookingConfirmed',
            BookingCancelled::class => 'handleBookingCancelled',
            BookingRescheduled::class => 'handleBookingRescheduled',
            BookingCheckedIn::class => 'handleBookingCheckedIn',
            BookingExpired::class => 'handleBookingExpired',
            BookingCompleted::class => 'handleBookingCompleted',
        ];
    }
}
