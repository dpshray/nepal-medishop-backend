<?php

namespace App\Models\Purchase;

use App\Enums\Purchase\OrderTypeEnum;
use App\Enums\Purchase\PaymentStatusEnum;
use App\Models\LoyalityPoint;
use App\Models\Point\CouponCode;
use App\Models\Traits\UuidModelTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

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
        'created_at',
        'used_coupon_code_id',
        'previous_price',
        'is_order_completely_assigned'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'order_type' => OrderTypeEnum::class,
        'payment_status' => PaymentStatusEnum::class
    ];

    function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    #ID of user that has ordered
    function user()
    {
        return $this->belongsTo(User::class);
    }

    function getCustomerNameAttribute() {
        return ($this->name) ?? $this->user->name;
    }

    function getMobNoAttribute() {
        return $this->mobile ?? $this->user->mobile_number;
    }
    
    function getMailAttribute() {
        return $this->email ?? $this->user->email;
    }

    /* function assignedVendor() {
        return $this->belongsTo(Vendor::class, 'assigned_vendor_id');
    } */

    function loyalityPoint()
    {
        return $this->hasOne(LoyalityPoint::class);
    }
    public function promoCode()
    {
        return $this->belongsTo(CouponCode::class,'used_coupon_code_id');
    }
}
