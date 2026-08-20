<?php

namespace App\Domains\WhatsApp\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Booking\Models\Booking;

class WhatsAppMessage extends Model
{
    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'booking_id', 'customer_id', 'provider', 'direction', 'message_type',
        'recipient', 'external_message_id', 'template_name', 'payload',
        'body', 'status', 'sent_at', 'delivered_at', 'read_at', 'failed_at',
        'error_message'
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'failed_at' => 'datetime'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
