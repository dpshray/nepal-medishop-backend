<?php

namespace App\Enums\Purchase;

enum PaymentMethodEnum: string
{
    case CASH_ON_DELIVERY = 'Cash on Delivery';
    // payment_method	cod / stripe / esewa	
}
