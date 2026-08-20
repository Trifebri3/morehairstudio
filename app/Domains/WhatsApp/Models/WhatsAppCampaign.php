<?php

namespace App\Domains\WhatsApp\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppCampaign extends Model
{
    protected $table = 'whatsapp_campaigns';

    protected $fillable = ['name', 'template_name', 'recipient_type', 'filters', 'scheduled_at', 'status'];

    protected $casts = [
        'filters' => 'array',
        'scheduled_at' => 'datetime'
    ];

    public function recipients()
    {
        return $this->hasMany(WhatsAppCampaignRecipient::class, 'campaign_id');
    }
}
