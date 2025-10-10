<?php

namespace App\Models\Purchase;

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
}
