<?php

namespace App\Models\Purchase;

use Illuminate\Database\Eloquent\Model;

class OrderItemProduct extends Model
{
    function orderItem() {
        return $this->belongsTo(OrderItem::class);
    }
}
