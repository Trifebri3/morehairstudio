<?php

namespace App\Domains\Review\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Booking\Models\Booking;
use App\Domains\Customer\Models\Customer;
use App\Domains\Outlet\Models\Outlet;
use App\Domains\Stylist\Models\Stylist;

class Review extends Model
{
    protected $fillable = [
        'booking_id', 'customer_id', 'outlet_id', 'stylist_id',
        'rating', 'review', 'status'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

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
}
