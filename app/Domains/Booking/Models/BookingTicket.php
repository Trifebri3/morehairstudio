<?php

namespace App\Domains\Booking\Models;

use Illuminate\Database\Eloquent\Model;

class BookingTicket extends Model
{
    protected $table = 'booking_tickets';

    protected $fillable = ['booking_id', 'ticket_code', 'passcode', 'qr_code_path', 'pdf_path'];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
