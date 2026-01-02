<?php

namespace App\Models\purchase;

use App\Models\VendorProductPrice;
use Illuminate\Database\Eloquent\Model;

class OrderItemBatchNumber extends Model
{
    //
    protected $fillable = [
        'order_item_id',
        'vendor_product_price_id',
        'product_variation_id',
        'quantity'
    ];

    function vendorProductPrice() {
        return $this->belongsTo(VendorProductPrice::class);
    }
}
