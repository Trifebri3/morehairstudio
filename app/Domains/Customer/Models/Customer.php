<?php

namespace App\Domains\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Booking\Models\Booking;
use App\Domains\Review\Models\Review;

use Illuminate\Notifications\Notifiable;

class Customer extends Model
{
    use Notifiable;
    protected $fillable = [
        'customer_code', 'phone', 'whatsapp_phone', 'name',
        'email', 'birth_date', 'gender', 'address', 'status',
        'tags', 'notes', 'loyalty_points', 'first_acquisition_source',
        'latest_acquisition_source', 'acquisition_metadata',
        'whatsapp_marketing_opt_in', 'email_marketing_opt_in'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'tags' => 'array',
        'acquisition_metadata' => 'array',
        'whatsapp_marketing_opt_in' => 'boolean',
        'email_marketing_opt_in' => 'boolean'
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function activities()
    {
        return $this->hasMany(CustomerActivity::class);
    }

    public function posTransactions()
    {
        return $this->hasMany(\App\Domains\POS\Models\PosTransaction::class);
    }
}
