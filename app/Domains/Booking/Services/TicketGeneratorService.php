<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Models\BookingTicket;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TicketGeneratorService
{
    /**
     * Generate or retrieve a booking ticket, including QR and PDF document.
     */
    public static function generateForBooking(Booking $booking): BookingTicket
    {
        // 1. Get or create ticket (idempotency)
        $ticket = BookingTicket::firstOrCreate([
            'booking_id' => $booking->id
        ], [
            'ticket_code' => 'TKT-' . strtoupper(Str::random(10)),
            'passcode' => str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
        ]);

        // Ensure folders exist
        if (!Storage::disk('public')->exists('tickets')) {
            Storage::disk('public')->makeDirectory('tickets');
        }

        // 2. Generate local QR Code if missing
        if (empty($ticket->qr_code_path)) {
            $verificationUrl = url("/booking/ticket/{$ticket->ticket_code}");
            $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($verificationUrl);

            try {
                // Fetch and save QR Code locally
                $qrContent = @file_get_contents($qrApiUrl);
                if ($qrContent) {
                    $qrFilename = "tickets/qr_{$ticket->id}.png";
                    Storage::disk('public')->put($qrFilename, $qrContent);
                    $ticket->update(['qr_code_path' => '/storage/' . $qrFilename]);
                } else {
                    $ticket->update(['qr_code_path' => $qrApiUrl]);
                }
            } catch (\Exception $e) {
                Log::error("QR Generation failed: " . $e->getMessage());
                $ticket->update(['qr_code_path' => $qrApiUrl]);
            }
        }

        // 3. Generate PDF if missing
        if (empty($ticket->pdf_path)) {
            try {
                $outlet = $booking->outlet;
                $customer = $booking->customer;
                $stylist = $booking->stylist;
                $items = $booking->items()->with('service')->get();
                
                $qrPathOrUrl = filter_var($ticket->qr_code_path, FILTER_VALIDATE_URL) 
                    ? $ticket->qr_code_path 
                    : public_path($ticket->qr_code_path);

                $data = [
                    'booking' => $booking,
                    'ticket' => $ticket,
                    'outlet' => $outlet,
                    'customer' => $customer,
                    'stylist' => $stylist,
                    'items' => $items,
                    'qrCodeUrl' => $qrPathOrUrl
                ];

                $html = view('emails.ticket_pdf', $data)->render();
                
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
                $pdfFilename = "tickets/ticket_{$ticket->id}.pdf";
                Storage::disk('public')->put($pdfFilename, $pdf->output());
                
                $ticket->update(['pdf_path' => '/storage/' . $pdfFilename]);
            } catch (\Exception $e) {
                Log::error("PDF Ticket Generation failed: " . $e->getMessage());
            }
        }

        return $ticket;
    }
}
