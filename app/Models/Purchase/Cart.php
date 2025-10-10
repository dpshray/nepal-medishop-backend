<?php

namespace App\Models\Purchase;

use App\Models\Package;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'item_type',
        'item_id',
        'variant_id',
        'item_name',
        'item_slug',
        'brand_name',
        'variant_name',
        'image',
        'quantity',
        'price',
        'subtotal',
        'created_at'
    ];
}
