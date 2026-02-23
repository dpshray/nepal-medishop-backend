<?php

namespace App\Enums\Purchase;

enum PaymentMethodEnum: string
{
    case CASH_ON_DELIVERY = 'Cash on Delivery';
    case ESEWA = 'esewa';
    // payment_method	cod / stripe / esewa	
}
