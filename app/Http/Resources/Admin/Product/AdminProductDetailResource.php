<?php

namespace App\Http\Resources\Admin\Product;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

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
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'brand' => $this->whenLoaded('brand', fn() => ['id' => $this->brand->id, 'name' => $this->brand->name]),
            'generic_product' => [
                'generic_product_name' => $this->genericProductName->name, 
                'generic_product_id' => $this->genericProductName->id
            ],
            'description' => $this->description,
            'added_date' => $this->created_at,
            'prescription_required' => (bool) $this->prescription_required,
            'no_of_vendors' => (int) $this->whenCounted('productVendors', fn() => $this->product_vendors_count),
            'total_units_in_stock' => ($this->productVendorPrices) ? $this->productVendorPrices->sum('units_in_stock') : 0,
            'categories' => $this->whenLoaded('categories', fn() => $this->categories->map(fn($item) => ['id' => $item->id, 'name' => $item->name])),
            'tags' => $this->whenLoaded('tags', fn() => $this->tags->map(fn($item) => ['id' => $item->id, 'name' => $item->name])),
            'variations' => $this->whenLoaded('variations', fn() => $this->variations->map(fn($item) => [
                'variation_id' => $item->id,
                'name' => $item->name,
                'size_value' => (float)$item->size_value,
                'size_unit' => $item->size_unit,
                'admin_price' => (float)$item->platform_price,
                'units_in_stock' => $item->vendorProductPrices->sum('units_in_stock')
            ])),
            'health_conditions' => $this->healthConditions->map(fn($item) => ['name' => $item->name, 'id' => $item->id]),
            'featured_image' => $this->whenLoaded('media', fn() => [
                'id' => $this->getFirstMedia(Product::PRODUCT_FEATURE)->id,
                'url' => $this->getFirstMediaUrl(Product::PRODUCT_FEATURE)
            ]),
            'gallery_images' => $this->whenLoaded('media', fn() => $this->getMedia(Product::PRODUCT_GALLERY)->map(fn($item) => [
                'id' => $item->id,
                'url' => $item->getUrl()
            ])),
        ];
    }
}
