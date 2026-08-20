<?php

namespace App\Domains\POS\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'sku', 'name', 'description', 'purchase_price',
        'selling_price', 'stock', 'min_stock', 'is_active'
    ];

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }
}
