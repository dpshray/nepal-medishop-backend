<?php

namespace App\Traits;

trait HelperTrait
{
    function calculateDiscountPrice($price, $discount_price = null){
        $previous_price = null;
        $price = (float) $price;
        if ($discount_price) {
            $previous_price = (float) $price;
            $price = (float)$discount_price;
        }
        return ['price' => $price, 'previous_price' => $previous_price];
    }
}
