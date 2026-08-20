<?php

namespace App\Domains\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class BookingStatusHistory extends Model
{
    protected $table = 'booking_status_histories';

    protected $fillable = [
        'booking_id', 'status', 'changed_by', 'reason'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
