<?php

namespace App\Models\Purchase;

use App\Models\ProductVariation;
use Illuminate\Database\Eloquent\Model;

class OrderItemProduct extends Model
{
    function order() {
        return $this->belongsTo(Order::class);
    }
    
    function orderItem() {
        return $this->belongsTo(OrderItem::class);
    }

    function variation() {
        return $this->belongsTo(ProductVariation::class,'product_variation_id');
    }

    #assigned batch number of product
    function batchNumbers() {
        return $this->hasMany(OrderItemProductBatchNumber::class);
    }
}
