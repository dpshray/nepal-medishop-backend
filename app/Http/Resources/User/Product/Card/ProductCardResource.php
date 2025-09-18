<?php

namespace App\Http\Resources\User\Product\Card;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $item_price = $this->cheapestVariation;
        // dd($this->product);
        $platform_price = $item_price->platform_price;
        $discount_price = null;
        if ($item_price->platform_discount_price) {
            $discount_percent = (($item_price->price - $item_price->platform_discount_price)/ $item_price->price) * 100;
            $discount_price = round($platform_price + ($discount_percent * $platform_price)/100, 2);  
        }
        return [
            'name' => $this->name,
            'brand' => $this->whenLoaded('brand', fn() => $this->brand->name),
            'rating' => (float) $this->rating,
            'price' => (float) $platform_price,
            'previous_price' => $discount_price,
            'feature_image' => $this->whenLoaded('product', fn() => $this->product->getFirstMediaUrl(Product::PRODUCT_FEATURE))
        ];
    }
}
