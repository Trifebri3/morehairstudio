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
        'outlet_id', 'user_id', 'name', 'slug', 'photo', 'photo_path', 'bio',
        'specialization', 'rating', 'status', 'phone', 'instagram', 'tiktok'
    ];

    protected $casts = [
        'rating' => 'decimal:2',
    ];

    public function getPublicProfileUrlAttribute(): string
    {
        return url('/' . ($this->slug ?: \Illuminate\Support\Str::slug($this->name)));
    }

    public function getDisplayPhotoAttribute(): string
    {
        if ($this->photo_path) {
            return '/storage/' . ltrim($this->photo_path, '/');
        }
        if ($this->photo) {
            if (str_starts_with($this->photo, 'http')) {
                return $this->photo;
            }
            if (str_starts_with($this->photo, 'storage/')) {
                return '/' . $this->photo;
            }
            if (str_starts_with($this->photo, '/storage/')) {
                return $this->photo;
            }
            return str_starts_with($this->photo, '/') ? $this->photo : '/' . $this->photo;
        }
        return 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . urlencode($this->slug ?: $this->name);
    }

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
