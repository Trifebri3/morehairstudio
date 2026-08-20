<?php

namespace App\Domains\Attendance\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Stylist\Models\Stylist;

class Attendance extends Model
{
    protected $fillable = [
        'stylist_id', 'date', 'clock_in', 'clock_out',
        'status', 'location_lat', 'location_lng', 'device_info'
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'location_lat' => 'decimal:7',
        'location_lng' => 'decimal:7',
    ];

    public function stylist()
    {
        return $this->belongsTo(Stylist::class);
    }
}
