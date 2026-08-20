<?php

namespace App\Domains\POS\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Outlet\Models\Outlet;
use App\Domains\Customer\Models\Customer;
use App\Domains\Booking\Models\Booking;
use App\Domains\Payment\Models\Payment;
use App\Domains\Stylist\Models\Stylist;

class PosTransaction extends Model
{
    protected $fillable = [
        'transaction_number', 'outlet_id', 'customer_id', 'booking_id',
        'staff_id', 'subtotal', 'discount', 'tax', 'grand_total',
        'payment_status', 'status', 'payment_method', 'transaction_reference',
        'notes', 'completed_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function stylist()
    {
        return $this->belongsTo(Stylist::class, 'staff_id');
    }

    public function items()
    {
        return $this->hasMany(PosTransactionItem::class, 'transaction_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'transaction_id');
    }
}
