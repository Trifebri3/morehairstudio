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

        // Deliver QR Code image & PDF ticket immediately on booking creation
        if (CommunicationService::isWhatsAppEnabled() && $booking->customer) {
            if (!empty($ticket->qr_code_path)) {
                $qrUrl = asset($ticket->qr_code_path);
                CommunicationService::sendWhatsAppImage(
                    $booking->customer->phone,
                    $qrUrl,
                    $booking->id
                );
            }
            if (!empty($ticket->pdf_path)) {
                $pdfUrl = asset($ticket->pdf_path);
                CommunicationService::sendWhatsAppDocument(
                    $booking->customer->phone,
                    $pdfUrl,
                    "Ticket-{$booking->booking_code}.pdf",
                    $booking->id
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

        // 3. Deliver PDF ticket and QR Code if enabled
        if (CommunicationService::isWhatsAppEnabled() && $booking->customer) {
            if (!empty($ticket->qr_code_path)) {
                $qrUrl = asset($ticket->qr_code_path);
                CommunicationService::sendWhatsAppImage(
                    $booking->customer->phone,
                    $qrUrl,
                    $booking->id
                );
            }
            if (!empty($ticket->pdf_path)) {
                $pdfUrl = asset($ticket->pdf_path);
                CommunicationService::sendWhatsAppDocument(
                    $booking->customer->phone,
                    $pdfUrl,
                    "Ticket-{$booking->booking_code}.pdf",
                    $booking->id
                );
            }
        }

        if (CommunicationService::isEmailEnabled() && !empty($booking->customer->email) && !empty($ticket->pdf_path)) {
            $bookingDate = $booking->booking_date->format('d M Y');
            $subject = "Konfirmasi Tiket Reservasi - More Hair Studio";
            $emailBody = "
                <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
                    <h2 style='color: #0A3D91;'>MORE HAIR STUDIO</h2>
                    <p>Halo <strong>{$booking->customer->name}</strong>,</p>
                    <p>Pemesanan Anda telah berhasil dikonfirmasi. Berikut rincian reservasi Anda:</p>
                    <table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>
                        <tr><td style='padding: 5px 0; font-weight: bold;'>Kode Booking:</td><td>{$booking->booking_code}</td></tr>
                        <tr><td style='padding: 5px 0; font-weight: bold;'>Outlet:</td><td>{$booking->outlet->name}</td></tr>
                        <tr><td style='padding: 5px 0; font-weight: bold;'>Tanggal:</td><td>{$bookingDate}</td></tr>
                        <tr><td style='padding: 5px 0; font-weight: bold;'>Passcode Tiket:</td><td style='font-family: monospace; font-weight: bold;'>{$ticket->passcode}</td></tr>
                    </table>
                    <p>Detail tiket digital resmi Anda telah kami lampirkan dalam berkas PDF di email ini.</p>
                    <br>
                    <p style='font-size: 11px; color: #999;'>Ini adalah email otomatis. Mohon tidak membalas email ini.</p>
                </div>
            ";

            CommunicationService::sendEmail(
                $booking->customer->email,
                $subject,
                $emailBody,
                public_path($ticket->pdf_path),
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

                // Load template
                $template = DB::table('whatsapp_templates')
                    ->where('template_name', $auto->template_name)
                    ->where('is_active', true)
                    ->first();

                if ($template) {
                    $bodyText = $this->resolveMessageVariables($template->body, $booking, $ticket);
                    
                    // Dispatch text message
                    CommunicationService::sendWhatsApp($booking->customer->phone, $bodyText, $booking->id);

                    // Dispatch attachment file if configured in template
                    if (!empty($template->file_path)) {
                        $fileUrl = asset($template->file_path);
                        CommunicationService::sendWhatsAppDocument(
                            $booking->customer->phone,
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
        $ticketUrl = $ticket ? url("/booking/ticket/{$ticket->ticket_code}") : '';
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
