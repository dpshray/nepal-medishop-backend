<?php

namespace App\Models\Purchase;

use App\Models\Package;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'item_type',
        'item_id',
        'item_variant_id',
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

    function package() {
        return $this->belongsTo(Package::class,'item_id')->whereHas('orderItem', function ($q) {
            $q->where('item_type', Package::class);
        });
    }
}
