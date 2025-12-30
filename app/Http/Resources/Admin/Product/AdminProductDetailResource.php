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
        // return $this->healthConditions;
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
            'total_units_in_stock' => ($this->productVendorPrices) ? $this->productVendorPrices->sum(fn($q) => $q->stock_left) : 0,
            'categories' => $this->whenLoaded('categories', fn() => $this->categories->map(fn($item) => ['id' => $item->id, 'name' => $item->name])),
            'tags' => $this->whenLoaded('tags', fn() => $this->tags->map(fn($item) => ['id' => $item->id, 'name' => $item->name])),
            'variations' => $this->productVendors->where('vendor_id', Auth::id())->flatMap(fn($item) => 
                $item->vendorPrices->map(fn($itm) => [
                    'variant_id' => $itm->product_variation_id,
                    'variant_name' => $itm->variation->name,
                    'variant_size_value' => (int)$itm->variation->size_value,
                    'variant_size_unit' => $itm->variation->size_unit,
                    'variant_admin_price' => (float)$itm->variation->platform_price,
                    'variant_units_in_stock' => (float)$itm->units_in_stock,
                    "batch_number" => (int)$itm->batch_number,
                    "manufacture" => $itm->manufacture,
                    "expiry_date" => $itm->expiry_date,
                ])
            )->values(),
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
