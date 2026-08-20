<?php

namespace App\Domains\Service\Models;

use Illuminate\Database\Eloquent\Model;

class OutletService extends Model
{
    protected $table = 'outlet_services';

    protected $fillable = [
        'outlet_id', 'service_id', 'price', 'duration', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'duration' => 'integer',
    ];
}
