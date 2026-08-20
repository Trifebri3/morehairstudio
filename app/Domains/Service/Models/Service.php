<?php

namespace App\Domains\Service\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Outlet\Models\Outlet;

class Service extends Model
{
    protected $fillable = [
        'service_category_id', 'name', 'slug', 'description',
        'default_price', 'default_duration', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_price' => 'decimal:2',
        'default_duration' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function outlets()
    {
        return $this->belongsToMany(Outlet::class, 'outlet_services')
                    ->withPivot('price', 'duration', 'is_active')
                    ->withTimestamps();
    }
}
