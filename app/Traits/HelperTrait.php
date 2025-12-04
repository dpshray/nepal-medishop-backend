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

    function calculatePriceBeforeDiscount($price, $discounts = []) {
        $previous_price = null;
        $price = (float) $price;
        if (!empty($discounts)) {
            $previous_price = $price;
            $tot_discount_percent = array_sum($discounts);
            $price = (float) ($price + (($tot_discount_percent * $price) / 100));
        }
        return ['price' => $price, 'previous_price' => $previous_price];
    }

    function generateOrderCode() {
        return strtoupper(str()->random(5));
    }
}
