<?php

namespace App\Domains\WhatsApp\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppConfiguration extends Model
{
    protected $table = 'whatsapp_configurations';

    protected $fillable = ['provider', 'config', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
