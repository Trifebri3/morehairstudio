<?php

namespace App\Domains\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Booking\Models\Booking;

class Payment extends Model
{
    protected $fillable = [
        'booking_id', 'transaction_id', 'payment_method', 'transaction_reference',
        'amount', 'status', 'paid_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function posTransaction()
    {
        return $this->belongsTo(\App\Domains\POS\Models\PosTransaction::class, 'transaction_id');
    }
}
