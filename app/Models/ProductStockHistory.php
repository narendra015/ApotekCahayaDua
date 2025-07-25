<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStockHistory extends Model
{
    protected $fillable = ['product_id', 'qty', 'price', 'expired_date'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
