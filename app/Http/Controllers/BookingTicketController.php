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
        $ticket = BookingTicket::where('ticket_code', $code)
            ->with(['booking.customer', 'booking.outlet', 'booking.stylist', 'booking.items.service'])
            ->firstOrFail();

        $booking = $ticket->booking;

        return view('public.ticket-view', compact('ticket', 'booking'));
    }
}
