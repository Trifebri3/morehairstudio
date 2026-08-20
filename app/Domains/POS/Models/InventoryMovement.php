<?php

namespace App\Domains\POS\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_id', 'type', 'quantity', 'reference_type',
        'reference_id', 'notes'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
