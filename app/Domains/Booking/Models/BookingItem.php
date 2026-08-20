<?php

namespace App\Domains\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Service\Models\Service;

class BookingItem extends Model
{
    protected $fillable = [
        'booking_id', 'service_id', 'price', 'duration', 'start_time', 'end_time'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration' => 'integer',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
