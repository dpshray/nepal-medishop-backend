<?php

namespace App\Http\Resources\Admin\Product;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminProductDetailResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'brand' => $this->whenLoaded('brand', fn() => $this->brand->name),
            'description' => $this->description,
            'added_date' => $this->created_at,
            'no_of_vendors' => $this->whenCounted('productVendors', fn() => $this->product_vendors_count),
            'categories' => $this->whenLoaded('categories', fn() => $this->categories->pluck('name')),
            'tags' => $this->whenLoaded('tags', fn() => $this->tags->pluck('name')),
            'variations' => $this->whenLoaded('variations', fn() => $this->variations->map(fn($item) => [
                'variation_id' => $item->id,
                'size_value' => (float)$item->size_value,
                'size_unit' => $item->size_unit,
                'admin_price' => (float)$item->platform_price
            ])),
            'featured_image' => $this->whenLoaded('media', fn() => [
                'id' => $this->getFirstMedia(Product::PRODUCT_FEATURE)->id,
                'url' => $this->getFirstMediaUrl(Product::PRODUCT_FEATURE)
            ]),
            'gallery_images' => $this->whenLoaded('media', fn() => $this->getMedia(Product::PRODUCT_GALLERY)->map(fn($item) => [
                'id' => $item->id,
                'url' => $item->getUrl()
            ]))
        ];
    }
}
