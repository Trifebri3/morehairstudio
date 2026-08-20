<?php

namespace App\Domains\WhatsApp\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Customer\Models\Customer;

class WhatsAppCampaignRecipient extends Model
{
    protected $table = 'whatsapp_campaign_recipients';

    protected $fillable = ['campaign_id', 'customer_id', 'status', 'error_message', 'sent_at'];

    protected $casts = [
        'sent_at' => 'datetime'
    ];

    public function campaign()
    {
        return $this->belongsTo(WhatsAppCampaign::class, 'campaign_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
