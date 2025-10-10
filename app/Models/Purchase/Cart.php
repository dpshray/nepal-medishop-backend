<?php

namespace App\Models\Purchase;

use App\Models\Package;
use App\Models\Product;
use App\Models\Traits\UuidModelTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use UuidModelTrait;
    
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

    function user() {
        return $this->belongsTo(User::class);
    }
}
