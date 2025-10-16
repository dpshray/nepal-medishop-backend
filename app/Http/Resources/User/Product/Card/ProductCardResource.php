<?php

namespace App\Http\Resources\User\Product\Card;

use App\Models\Product;
use App\Traits\HelperTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductCardResource extends JsonResource
{
    use HelperTrait;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $item = $this->cheapestVariation;
        /* $platform_price = $item->platform_price;
        $previous_price = null;
        if ($item->platform_discount_price) {
            $previous_price = (float) $platform_price;
            $platform_price = $item->platform_discount_price;
        } */
        ['price' => $price, 'previous_price' => $previous_price] = $this->calculateDiscountPrice($item->platform_price, $this->discount_percent);

        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'brand' => $this->whenLoaded('brand', fn() => $this->brand->name),
            'rating' => (float) $this->rating,
            'price' => $price,
            'previous_price' => $previous_price,
            'feature_image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl(Product::PRODUCT_FEATURE)),
            'liked' => $this->whenLoaded('likes', fn() => $this->likes->count() ? true : false),
            'variations' => $this->whenLoaded('variations', fn() => $this->variations->map(function ($item) {
                ['price' => $price, 'previous_price' => $previous_price] = $this->calculateDiscountPrice($item->platform_price, $this->discount_percent);

                return [
                    'variation_id' => $item->id,
                    'name' => $item->name,
                    'size_value' => (float)$item->size_value,
                    'size_unit' => $item->size_unit,
                    'price' => $price,
                    'previous_price' => $previous_price
                ];
            }))
        ];
    }
}
