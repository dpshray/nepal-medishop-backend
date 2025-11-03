<?php

namespace App\Http\Resources\User\Product;

use App\Models\Product;
use App\Traits\HelperTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserLikeResource extends JsonResource
{
    use HelperTrait;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product = $this->product;
        $item = $product->cheapestVariation;

        ['price' => $price, 'previous_price' => $previous_price, 'discount_percent' => $discount_percent] = $item->original_price;

        return [
            'name' => $product->name,
            'slug' => $product->slug,
            'brand' => $product->brand->name,
            'rating' => (float) $product->rating,
            'price' => $price,
            'previous_price' => $previous_price,
            'discount_percent' => $discount_percent, 
            'feature_image' => $product->getFirstMediaUrl(Product::PRODUCT_FEATURE),
            'liked' => $this->whenLoaded('likes', fn() => $product->likes->count() ? true : false),
            'variations' => $product->variations->map(
                function ($item) use($product) {
                    ['price' => $v_price, 'previous_price' => $v_previous_price] = $item->original_price;
                    return [
                        'variation_id' => $item->id,
                        'name' => $item->name,
                        'size_value' => (float)$item->size_value,
                        'size_unit' => $item->size_unit,
                        'price' => $v_price,
                        'previous_price' => $v_previous_price
                    ];
                }
            )
        ];
    }
}
