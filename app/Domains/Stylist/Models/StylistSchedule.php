<?php

namespace App\Domains\Stylist\Models;

use Illuminate\Database\Eloquent\Model;

class StylistSchedule extends Model
{
    protected $fillable = [
        'stylist_id', 'day_of_week', 'start_time', 'end_time',
        'break_start', 'break_end', 'is_working'
    ];

    protected $casts = [
        'is_working' => 'boolean',
        'day_of_week' => 'integer',
    ];

    public function stylist()
    {
        return $this->belongsTo(Stylist::class);
    }
}
