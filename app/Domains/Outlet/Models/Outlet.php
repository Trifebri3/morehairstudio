<?php

namespace App\Domains\Outlet\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Service\Models\Service;
use App\Domains\Stylist\Models\Stylist;
use App\Domains\Booking\Models\Booking;
use App\Domains\Review\Models\Review;

class Outlet extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'address', 'phone', 'whatsapp',
        'latitude', 'longitude', 'opening_hours', 'status', 'gallery',
        'booking_lead_time_hours', 'checkin_grace_period_active', 'checkin_grace_period_minutes',
        'clock_out_start_time', 'clock_out_end_time', 'map_iframe'
    ];

    protected $casts = [
        'opening_hours' => 'array',
        'gallery' => 'array',
    ];

    public function stylists()
    {
        return $this->hasMany(Stylist::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'outlet_services')
                    ->withPivot('price', 'duration', 'is_active')
                    ->withTimestamps();
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
