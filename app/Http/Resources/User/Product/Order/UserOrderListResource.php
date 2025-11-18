<?php

namespace App\Http\Resources\User\Product\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserOrderListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            "uuid" => $this->uuid,
            "order_code" => $this->order_code,
            "address" => $this->address,
            "address" => $this->address,
            "previous_price" => (float)$this->previous_price,
            'promo_code' => $this->promoCode ? [
                'code' => $this->promoCode->code,
                'discount' => $this->promoCode->discount_percent,
            ] : null,
            "price" => (float) $this->price,
            "gift_wrap" => (bool) $this->gift_wrap,
            "payment_method" => $this->payment_method,
            "payment_status" => $this->payment_status,
            "order_status" => $this->status,
            "created_at" => $this->created_at->format('Y/m/d'),
        ];
    }
}
