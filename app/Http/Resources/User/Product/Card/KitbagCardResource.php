<?php

namespace App\Http\Resources\User\Product\Card;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KitbagCardResource extends JsonResource
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
            "item_uuid" => $this->uuid,
            "item_name" => $this->product->name,
            "item_slug" => $this->product->slug,
            "brand_name" => $this->product->brand->name,
            "variant_name" => $this->variation->name,
            "variant_id" => $this->variation->id,
            "image" => $this->product->getFirstMediaUrl(Product::PRODUCT_FEATURE),
            "quantity" => (integer) $this->quantity,
            "price" => (float) $this->variation->platform_price,
            "subtotal" => (float) ($this->quantity * $this->variation->platform_price),
        ];
    }
}
