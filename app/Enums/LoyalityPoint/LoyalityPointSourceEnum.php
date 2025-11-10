<?php

namespace App\Enums\LoyalityPoint;

enum LoyalityPointSourceEnum:string
{
    case ORDER_PURCHASE = "ORDER_PURCHASE";
    case SIGNUP_BONUS = "SIGNUP_BONUS";
}
