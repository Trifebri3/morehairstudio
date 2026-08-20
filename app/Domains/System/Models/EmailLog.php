<?php

namespace App\Domains\System\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Booking\Models\Booking;
use App\Domains\Customer\Models\Customer;

class EmailLog extends Model
{
    protected $table = 'email_logs';

    protected $fillable = ['booking_id', 'customer_id', 'recipient', 'subject', 'status', 'error_message'];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
