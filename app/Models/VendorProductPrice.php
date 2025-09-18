<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorProductPrice extends Model
{
    protected $fillable = [
        "size_value",
        "size_unit",
        "platform_price",
        "platform_discount_price",
    ];
    function ProductVendor(){
        return $this->belongsTo(ProductVendor::class);
    }
}
