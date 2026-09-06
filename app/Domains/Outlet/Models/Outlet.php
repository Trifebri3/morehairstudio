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
        'attendance_start_time', 'attendance_end_time',
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

    public function getGoogleMapsUrlAttribute(): string
    {
        if ($this->latitude && $this->longitude) {
            return "https://www.google.com/maps/search/?api=1&query={$this->latitude},{$this->longitude}";
        }

        return "https://www.google.com/maps/search/?api=1&query=" . urlencode($this->name . ' ' . $this->address);
    }

    public function getGoogleMapsEmbedUrlAttribute(): string
    {
        if (!empty($this->map_iframe)) {
            if (preg_match('/src="([^"]+)"/', $this->map_iframe, $match)) {
                return $match[1];
            }
        }

        if ($this->latitude && $this->longitude) {
            return "https://maps.google.com/maps?q={$this->latitude},{$this->longitude}&t=&z=16&ie=UTF8&iwloc=&output=embed";
        }

        return "https://maps.google.com/maps?q=" . urlencode($this->name . ' ' . $this->address) . "&t=&z=16&ie=UTF8&iwloc=&output=embed";
    }
}
