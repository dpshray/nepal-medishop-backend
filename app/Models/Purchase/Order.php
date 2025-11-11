<?php

namespace App\Models\Purchase;

use App\Enums\LoyalityPoint\LoyalityPointSourceEnum;
use App\Enums\LoyalityPoint\LoyalityPointStatusEnum;
use App\Enums\LoyalityPoint\LoyalityPointTypeEnum;
use App\Enums\Purchase\OrderStatusEnum;
use App\Enums\Purchase\OrderTypeEnum;
use App\Enums\Purchase\PaymentStatusEnum;
use App\Models\LoyalityPoint;
use App\Models\Traits\UuidModelTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

    public static function boot()
    {
        parent::boot();
        static::updating(function ($order) {
            $user = $order->user;
            $latest_approved_loyality_points = $user->latestApprovedLoyalityPoints;
            $balance_after = $earned_points = $order->price * LoyalityPoint::LOYALITY_POINTS; #FIRST TIME DEFAULT

            if ($order->status == OrderStatusEnum::PENDING) {
                foreach ($order->orderItems as $order_item) {
                    $order->assignedVendor
                        ->vendor
                        ->vendorProductPrices()
                        ->where('product_variation_id', $order_item['item_variant_id'])
                        ->increment('units_in_stock', $order_item['quantity']);
                }
                $order->loyalityPoint()->delete();
            }elseif ($order->status == OrderStatusEnum::DELIVERED) {
                if ($order->loyalityPoint()->doesntExist()) {                    
                    if ($latest_approved_loyality_points) { # if previous approved loyality point exists
                        $balance_after = $latest_approved_loyality_points->balance_after + $earned_points;
                    }
                    /* Log::info([
                        $order->loyalityPoint()->doesntExist(),
                        $user,
                        $latest_approved_loyality_points,
                        $balance_after,
                    ]); */
                    foreach ($order->orderItems as $order_item) {                        
                        $order->assignedVendor
                            ->vendor
                            ->vendorProductPrices()
                            ->where('product_variation_id',$order_item['item_variant_id'])
                            ->decrement('units_in_stock', $order_item['quantity']);
                    }
                    $order->loyalityPoint()->create([
                        'user_id' => $order->user_id,
                        'points' => $earned_points,
                        'type' => LoyalityPointTypeEnum::EARN,
                        'source' => LoyalityPointSourceEnum::ORDER_PURCHASE,
                        'description' => 'loyality points earned from :'.LoyalityPointSourceEnum::ORDER_PURCHASE->value,
                        'status' => LoyalityPointStatusEnum::APPROVED,
                        'balance_after' => $balance_after,
                    ]);
                }
            }
        });
    }

    function orderItems() {
        return $this->hasMany(OrderItem::class);
    }

    #ID of user that has ordered
    function user() { 
        return $this->belongsTo(User::class);
    }

    function assignedVendor() {
        return $this->belongsTo(User::class, 'assigned_vendor_id');
    }

    function loyalityPoint() {
        return $this->hasOne(LoyalityPoint::class);
    }
}
