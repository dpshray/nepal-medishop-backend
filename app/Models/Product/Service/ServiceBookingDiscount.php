<?php

namespace App\Models\Product\Service;

use App\Enums\Purchase\DiscountEnum;
use Illuminate\Database\Eloquent\Model;

class ServiceBookingDiscount extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'type',
        'discount_amount',
        'code'
    ];

    protected $casts = [
        'type' => DiscountEnum::class,
    ];
}
