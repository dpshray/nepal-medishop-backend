<?php

namespace App\Models;

use App\Models\Traits\UuidModelTrait;
use Illuminate\Database\Eloquent\Model;

class Kitbag extends Model
{
    use UuidModelTrait;

    public $timestamps = false;
    protected $fillable = [
        'product_variation_id',
        'quantity',
        'product_id',
        'user_id',
    ];

    function product() {
        return $this->belongsTo(Product::class);
    }
    
    function variation() {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }
}
