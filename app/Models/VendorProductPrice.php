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
        "batch_number",
        "manufacture",
        "expiry_date"
    ];

    protected $hidden = [
        'deleted_at'
    ];

    public $timestamps = false;

    protected static function booted()
    {
        static::updated(function ($vpp) {
            $vpp->ProductVendor->touch(); // updates updated_at timestamp
        });
    }

    function ProductVendor()
    {
        return $this->belongsTo(ProductVendor::class);
    }

    function variation()
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    function scopeActive($qry) {
        return $qry->where('status',1);
    }
}
