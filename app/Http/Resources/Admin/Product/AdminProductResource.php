<?php

namespace App\Http\Resources\Admin\Product;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminProductResource extends JsonResource
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
            'uuid' => $this->uuid,
            'published' => (bool)$this->status,
            'name' => $this->name,
            'brand' => $this->whenLoaded('brand', fn() => $this->brand->name),
            'generic' => $this->whenLoaded('genericProductName', fn() => $this->genericProductName->name),
            'featured_image' => $this->whenLoaded('media', fn() => [
                'id' => $this->getFirstMedia(Product::PRODUCT_FEATURE)->id,
                'url' => $this->getFirstMediaUrl(Product::PRODUCT_FEATURE)
            ]),
            'health_conditions' => $this->whenLoaded('healthConditions', function () {
                return $this->healthConditions->map(fn($item) => [
                    'name' => $item->name,
                ]);
            }),
            'lowest_variant_price' => $this->whenLoaded('cheapestVariation', fn() => (float)$this->cheapestVariation->platform_price),
            'total_stock' => $this->whenLoaded('productVendorPrices', fn() => (int)$this->productVendorPrices()->sum('units_in_stock')),
            'variations' => $this->whenLoaded('variations', function () {
                return $this->variations->map(fn($item) => [
                    'id'         => $item->id,
                    // 'name'       => $item->name,
                    'size_value' => (float) $item->size_value,
                    'size_unit'  => $item->size_unit,
                    'platform_price' => (float) $item->platform_price,
                    'form_type' => $item?->form_type,
                    'package_type' => $item?->package_type,
                    'package_size' => $item?->package_size,
                    'strength' => $item?->strength,
                    // 'image' => $item?->getFirstMediaUrl(ProductVariation::VARIATION_IMAGE),
                ]);
            })

        ];
    }
}
