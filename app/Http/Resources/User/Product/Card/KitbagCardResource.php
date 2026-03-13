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
        $price = $this->variation->platform_price - ($this->variation->platform_price * ($this->product->discount_percent / 100));
        return [
            "item_uuid" => $this->uuid,
            "item_name" => $this->product->name,
            "item_slug" => $this->product->slug,
            "brand_name" => $this->product->brand->name,
            "variant_name" => $this->variation->strength,
            "variant_id" => $this->variation->id,
            "form_type" => $this->variation->form_type,
            "package_type" => $this->variation->package_type . ' ' . $this->variation->package_size . ' ' . $this->variation->size_unit,
            'isPrescriptionRequired' => (bool) $this->product->prescription_required,
            "image" => $this->product->getFirstMediaUrl(Product::PRODUCT_FEATURE),
            "quantity" => (int) $this->quantity,
            "price" => (float)$price,
            "previous_price" => $this->product->discount_percent !== null ? (float) $this->variation->platform_price : null,
            "subtotal" => (float) ($this->quantity * $price),
        ];
    }
}
