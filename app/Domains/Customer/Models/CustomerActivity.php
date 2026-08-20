<?php

namespace App\Domains\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Outlet\Models\Outlet;

class CustomerActivity extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'customer_id', 'event_type', 'event_date', 'outlet_id',
        'source', 'reference_type', 'reference_id', 'metadata'
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'metadata' => 'array'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }
}
