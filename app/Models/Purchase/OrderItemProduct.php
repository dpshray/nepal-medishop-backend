<?php

namespace App\Models\Purchase;

use App\Models\ProductVariation;
use Illuminate\Database\Eloquent\Model;

class OrderItemProduct extends Model
{
    function orderItem() {
        return $this->belongsTo(OrderItem::class);
    }

    function variation() {
        return $this->belongsTo(ProductVariation::class,'product_variation_id');
    }
}
