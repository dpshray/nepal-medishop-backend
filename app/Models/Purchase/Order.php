<?php

namespace App\Models\Purchase;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_type',
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

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($item) {
            $item->order_code = Str::random(20);
        });
    }
    
    function orderItems() {
        return $this->hasMany(OrderItem::class);
    }
}
