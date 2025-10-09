<?php

namespace App\Models\Purchase;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name', 
        'email', 
        'mobile', 
        'address', 
        'description',
        'price',
        'payment_method',
        'payment_status',
        'status',
        'created_at'
    ];
    
    function orderItems() {
        return $this->hasMany(OrderItem::class);
    }
}
