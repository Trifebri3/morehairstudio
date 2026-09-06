<?php

namespace App\Http\Controllers;

use App\Domains\Booking\Models\BookingTicket;
use Illuminate\Http\Request;

class BookingTicketController extends Controller
{
    /**
     * Display the digital ticket details.
     */
    public function show($code)
    {
        $cleanCode = strtoupper(trim($code));

        $ticket = BookingTicket::where('ticket_code', $cleanCode)
            ->orWhere('ticket_code', $code)
            ->orWhereHas('booking', function ($q) use ($cleanCode) {
                $q->where('booking_code', $cleanCode)
                  ->orWhere('booking_token', $cleanCode)
                  ->orWhere('booking_code', 'like', "%-{$cleanCode}");
            })
            ->with(['booking.customer', 'booking.outlet', 'booking.stylist', 'booking.items.service'])
            ->first();

        if (!$ticket) {
            // Check if booking exists but ticket hasn't been generated
            $booking = \App\Domains\Booking\Models\Booking::where('booking_code', $cleanCode)
                ->orWhere('booking_token', $cleanCode)
                ->orWhere('booking_code', 'like', "%-{$cleanCode}")
                ->first();

            if ($booking) {
                $ticket = \App\Domains\Booking\Services\TicketGeneratorService::generateForBooking($booking);
                $ticket->load(['booking.customer', 'booking.outlet', 'booking.stylist', 'booking.items.service']);
            }
        }

        if (!$ticket) {
            abort(404, 'Tiket atau Kode Reservasi tidak ditemukan.');
        }

        $booking = $ticket->booking;

        return view('public.ticket-view', compact('ticket', 'booking'));
    }
}
