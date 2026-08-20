<?php

namespace App\Domains\Promotion\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'promo_code', 'discount_type', 'discount_value', 'minimum_transaction',
        'maximum_discount', 'start_at', 'end_at', 'usage_limit', 'usage_count',
        'customer_limit', 'outlet_scope', 'service_scope'
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'minimum_transaction' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'customer_limit' => 'integer',
        'outlet_scope' => 'array',
        'service_scope' => 'array',
    ];
}
