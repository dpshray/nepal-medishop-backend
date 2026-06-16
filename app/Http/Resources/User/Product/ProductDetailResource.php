<?php

namespace App\Http\Resources\User\Product;

use App\Models\Product;
use App\Models\ProductVariation;
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
        $item = $this->cheapestVariation;
        ['price' => $price, 'previous_price' => $previous_price, 'discount_percent' => $discount_percent] = $item->original_price;

        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'brand' => $this->whenLoaded('brand', fn() => $this->brand->name),
            'description' => $this->description,
            'added_date' => $this->created_at->format('Y-m-d'),
            'generic' => $this->whenLoaded('genericProductName', fn() => $this->genericProductName->name),
            'health_conditions' => $this->whenLoaded('healthConditions', function () {
                return $this->healthConditions->map(fn($item) => [
                    'name' => $item->name,
                ]);
            }),
            'isPrescriptionRequired' => (bool) $this->prescription_required,
            'rating' => (float) $this->rating,
            'no_of_vendors' => $this->whenCounted('productVendors', fn() => $this->product_vendors_count),
            'categories' => $this->whenLoaded('categories', fn() => $this->categories->pluck('name')),
            'tags' => $this->whenLoaded('tags', fn() => $this->tags->pluck('name')),
            'discount_percent' => $discount_percent,
            'variations' => $this->whenLoaded('variations', fn() => $this->variations->map(function ($item) use (&$discount_percent_copy) {
                ['price' => $price, 'previous_price' => $previous_price, 'discount_percent' => $discount_percent] = $item->original_price;
                $discount_percent_copy = $discount_percent;
                return [
                    'variation_id' => $item->id,
                    // 'name' => $item->name,
                    'size_value' => (float)$item->size_value,
                    'size_unit' => $item->size_unit,
                    'price' => $price,
                    'previous_price' => $previous_price,
                    'stock' => $item->vendorProductPrices->sum('units_in_stock'),
                    'form_type' => $item?->form_type,
                    'package_type' => $item?->package_type,
                    'package_size' => $item?->package_size,
                    'strength' => $item?->strength,
                    // 'image' => $item->getFirstMediaUrl(ProductVariation::VARIATION_IMAGE),
                ];
            })),
            'featured_image' => $this->whenLoaded('media', fn() => $this->getFirstMedia(Product::PRODUCT_FEATURE)->getUrl()),
            'gallery_images' => $this->whenLoaded('media', fn() => $this->getMedia(Product::PRODUCT_GALLERY)->map(fn($item) => $item->getUrl())),
            'liked' => $this->whenLoaded('likes', fn() => $this->likes->count() ? true : false)
        ];
    }
}
