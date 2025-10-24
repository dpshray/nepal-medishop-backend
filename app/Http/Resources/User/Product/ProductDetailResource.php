<?php

namespace App\Http\Resources\User\Product;

use App\Models\Product;
use App\Traits\HelperTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
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

        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'brand' => $this->whenLoaded('brand', fn() => $this->brand->name),
            'description' => $this->description,
            'added_date' => $this->created_at->format('Y-m-d'),
            'rating' => (float) $this->rating,
            'no_of_vendors' => $this->whenCounted('productVendors', fn() => $this->product_vendors_count),
            'categories' => $this->whenLoaded('categories', fn() => $this->categories->pluck('name')),
            'tags' => $this->whenLoaded('tags', fn() => $this->tags->pluck('name')),
            'variations' => $this->whenLoaded('variations', fn() => $this->variations->map(function($item){
                ['price' => $price, 'previous_price' => $previous_price] = $item->original_price;

                return [
                    'variation_id' => $item->id,
                    'name' => $item->name,
                    'size_value' => (float)$item->size_value,
                    'size_unit' => $item->size_unit,
                    'price' => $price,
                    'previous_price' => $previous_price
                ];
            })),
            'featured_image' => $this->whenLoaded('media', fn() => $this->getFirstMedia(Product::PRODUCT_FEATURE)->getUrl()),
            'gallery_images' => $this->whenLoaded('media', fn() => $this->getMedia(Product::PRODUCT_GALLERY)->map(fn($item) => $item->getUrl())),
            'liked' => $this->whenLoaded('likes', fn() => $this->likes->count() ? true : false)
        ];
    }
}
