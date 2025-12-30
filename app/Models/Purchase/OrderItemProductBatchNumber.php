<?php

namespace App\Models\Purchase;

use App\Models\VendorProductPrice;
use Illuminate\Database\Eloquent\Model;

class OrderItemProductBatchNumber extends Model
{
    function vendorProductPrice() {
        return $this->belongsTo(VendorProductPrice::class);
    }

    function orderItemProduct() {
        return $this->belongsTo(OrderItemProduct::class);
    }
}
