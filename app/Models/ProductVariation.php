<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariation extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    public $timestamps = false;
    
    protected $fillable = [
        'name',
        'size_value',
        'size_unit',
        'platform_price',
        'platform_discount_price'
    ];
    
    function product(){
        return $this->belongsTo(Product::class);
    }


    function getOriginalPriceAttribute(): array
    {
        $product = $this->product;
        $price = (float) $this->platform_price;
        $previous_price = null;
        if (!empty($product->discount_percent)) {
            $previous_price = $price;
            $price = (float) ($price - (($product->discount_percent * $price) / 100));
        }elseif ($product->categories->firstWhere('discount_percent','!=',null)) {
            $discount_percent = $product->categories->firstWhere('discount_percent', '!=', null)->discount_percent;
            $previous_price = $price;
            $price = (float) ($price - (($discount_percent * $price) / 100));
        }
        return ['price' => $price, 'previous_price' => $previous_price];
    }
}
