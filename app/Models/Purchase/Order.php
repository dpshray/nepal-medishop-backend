<?php

namespace App\Models\Purchase;

use App\Enums\Purchase\OrderTypeEnum;
use App\Enums\Purchase\PaymentStatusEnum;
use App\Models\Traits\UuidModelTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use UuidModelTrait;

    public $timestamps = false;

    protected $fillable = [
        'user_type',
        'order_code',
        'order_type',
        'name',
        'email',
        'mobile',
        'address',
        'latitude',
        'longitude',
        'description',
        'price',
        'payment_method',
        'payment_status',
        'status',
        'gift_wrap',
        'gift_wrap_remarks',
        'gift_wrap_charge',
        'assigned_vendor_id',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'order_type' => OrderTypeEnum::class,
        'payment_status' => PaymentStatusEnum::class
    ];

    /* public static function boot()
    {
        parent::boot();
        static::creating(function ($item) {
            $item->order_code = Str::random(20);
        });
    } */

    function orderItems() {
        return $this->hasMany(OrderItem::class);
    }

    function user() {
        return $this->belongsTo(User::class);
    }
}
