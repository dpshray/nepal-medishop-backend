<?php

namespace App\Enums\Purchase;

enum DiscountEnum:string
{
    case PRODUCT_DISCOUNT = 'PRODUCT_DISCOUNT';
    case SERVICE_DISCOUNT = 'SERVICE_DISCOUNT';
    case COUPON_DISCOUNT = 'COUPON_DISCOUNT';
}
