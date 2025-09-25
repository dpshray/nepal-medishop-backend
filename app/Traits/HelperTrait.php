<?php

namespace App\Traits;

trait HelperTrait
{
    function calculateDiscountPrice($price, $discount_percent = null){
        $previous_price = null;
        $price = (float) $price;
        if ($discount_percent) {
            $previous_price = $price;
            $price = (float) ($price - (($discount_percent * $price)/100));
        }
        return ['price' => $price, 'previous_price' => $previous_price];
    }
}
