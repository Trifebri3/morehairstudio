<?php

namespace App\Domains\WhatsApp\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppTemplate extends Model
{
    protected $table = 'whatsapp_templates';

    protected $fillable = [
        'template_name', 'language', 'body', 'variables', 'is_active', 'file_path'
    ];

    protected $casts = [
        'variables' => 'json',
        'is_active' => 'boolean'
    ];
}
