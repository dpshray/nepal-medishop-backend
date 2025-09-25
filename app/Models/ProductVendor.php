<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVendor extends Model
{
    protected $fillable = [
        'product_id'
    ];
    public $timestamps = false;

    function vendorPrices(){
        return $this->hasMany(VendorProductPrice::class);
    }

    function product(){
        return $this->belongsTo(Product::class);
    }
}
