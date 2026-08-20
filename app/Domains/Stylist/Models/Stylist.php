<?php

namespace App\Domains\Stylist\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Outlet\Models\Outlet;
use App\Models\User;
use App\Domains\Booking\Models\Booking;
use App\Domains\Review\Models\Review;
use App\Domains\Attendance\Models\Attendance;

class Stylist extends Model
{
    protected $fillable = [
        'outlet_id', 'user_id', 'name', 'slug', 'photo', 'bio',
        'specialization', 'rating', 'status', 'phone'
    ];

    protected $casts = [
        'rating' => 'decimal:2',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedules()
    {
        return $this->hasMany(StylistSchedule::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
