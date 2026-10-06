<?php

namespace App\Domains\Stylist\Models;

use Illuminate\Database\Eloquent\Model;

class StylistLeave extends Model
{
    protected $fillable = [
        'stylist_id', 'start_date', 'end_date', 'status', 'reason'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function stylist()
    {
        return $this->belongsTo(Stylist::class);
    }
}
