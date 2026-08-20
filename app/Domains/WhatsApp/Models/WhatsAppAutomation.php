<?php

namespace App\Domains\WhatsApp\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Outlet\Models\Outlet;

class WhatsAppAutomation extends Model
{
    protected $table = 'whatsapp_automations';

    protected $fillable = ['name', 'event_type', 'template_name', 'delay_minutes', 'is_active', 'outlet_id'];

    protected $casts = [
        'is_active' => 'boolean',
        'delay_minutes' => 'integer'
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }
}
