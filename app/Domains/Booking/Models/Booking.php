<?php

namespace App\Domains\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Customer\Models\Customer;
use App\Domains\Outlet\Models\Outlet;
use App\Domains\Stylist\Models\Stylist;
use App\Domains\Payment\Models\Payment;
use App\Domains\Review\Models\Review;

class Booking extends Model
{
    protected $fillable = [
        'booking_code', 'booking_token', 'customer_id', 'outlet_id',
        'stylist_id', 'booking_date', 'status', 'source',
        'total_amount', 'discount_amount', 'net_amount', 'promo_code', 'notes'
    ];

    protected $casts = [
        'booking_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function stylist()
    {
        return $this->belongsTo(Stylist::class);
    }

    public function items()
    {
        return $this->hasMany(BookingItem::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(BookingStatusHistory::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
