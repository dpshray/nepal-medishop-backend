<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorProductPrice extends Model
{
    protected $fillable = [
        "product_vendor_id",
        "product_variation_id",
        "price",
        "units_in_stock",
    ];
    public $timestamps = false;

    function ProductVendor(){
        return $this->belongsTo(ProductVendor::class);
    }
}
