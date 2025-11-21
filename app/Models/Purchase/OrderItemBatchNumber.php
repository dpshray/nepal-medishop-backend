<?php

namespace App\Models\purchase;

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
}
