<?php

namespace App\Models\Purchase;

use App\Models\Package;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'item_type',
        'item_id',
        'item_name',
        'item_slug',
        'item_variant_id',
        'variant_name',
        'variant_size',
        'quantity',
        'price',
        'total',
        'created_at'
    ];

    function product(){
        return $this->belongsTo(Product::class,'item_id')->whereHas('orderItem', function ($q) {
            $q->where('item_type', Product::class);
        });
    }

    function productVariant() {
        return $this->belongsTo(ProductVariation::class,'item_variant_id');
    }

    function package() {
        return $this->belongsTo(Package::class,'item_id')->whereHas('orderItem', function ($q) {
            $q->where('item_type', Package::class);
        });
    }
}
