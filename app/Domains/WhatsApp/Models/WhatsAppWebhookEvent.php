<?php

namespace App\Domains\WhatsApp\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppWebhookEvent extends Model
{
    protected $table = 'whatsapp_webhook_events';

    protected $fillable = [
        'provider', 'event_id', 'event_type', 'payload', 'processed_at'
    ];

    protected $casts = [
        'payload' => 'json',
        'processed_at' => 'datetime'
    ];

    public $timestamps = false;
}
