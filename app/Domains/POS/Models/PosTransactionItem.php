<?php

namespace App\Domains\POS\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\Service\Models\Service;

class PosTransactionItem extends Model
{
    protected $fillable = [
        'transaction_id', 'item_type', 'item_id', 'quantity',
        'unit_price', 'discount', 'subtotal'
    ];

    public function transaction()
    {
        return $this->belongsTo(PosTransaction::class, 'transaction_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'item_id');
    }
}
