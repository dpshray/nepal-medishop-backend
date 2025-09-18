<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariation extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    
    protected $fillable = [
        'size_value',
        'size_unit',
        'platform_price',
        'platform_discount_price',
    ];
    
    function product(){
        return $this->belongsTo(Product::class);
    }
}
