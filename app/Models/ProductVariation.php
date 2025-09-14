<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    protected $fillable = [
        'size_value',
        'size_unit',
        'price',
        'discount_price',
    ];
    
    function product(){
        return $this->belongsTo(Product::class);
    }
}
