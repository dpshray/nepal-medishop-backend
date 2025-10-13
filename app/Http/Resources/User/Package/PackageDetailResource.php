<?php

namespace App\Http\Resources\User\Package;

use App\Models\Package;
use App\Models\Product;
use App\Traits\HelperTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageDetailResource extends JsonResource
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
        ['price' => $price, 'previous_price' => $previous_price] = $this->calculateDiscountPrice($this->price, $this->discount_percent);
        $categories = [];
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => round($price, 2),
            'discount_price' => round($previous_price, 2),
            'rating' => (float) $this->rating,
            'featured_image' => $this->whenLoaded('media', fn() => $this->getFirstMedia(Package::PACKAGE_FEATURED)->getUrl()),
            'gallery_images' => $this->whenLoaded('media', fn() => $this->getMedia(Package::PACKAGE_GALLERY)->map(fn($item) => $item->getUrl())),
            'products' => $this->packageProducts->map(function($item) use(&$categories){
                $variant = $item->variant;
                ['price' => $price, 'previous_price' => $previous_price] = $this->calculateDiscountPrice($variant->platform_price, $variant->discount_percent);
                
                $incoming_category = $variant->product->categories->map(fn($item) => ['name' => $item->name, 'slug' => $item->slug]);
                $categories = [...$incoming_category, ...$categories];
                return [
                    'image' => $variant->product->getFirstMedia(Product::PRODUCT_FEATURE)->getUrl(),
                    'product_name' => $variant->product->name,
                    'slug' => $variant->product->slug,
                    "size_value" =>  (float) $variant->size_value,
                    "size_unit" => $variant->size_unit,
                    'price' => $previous_price ?? $price,
                    'brand' => $variant->product->brand->name,
                    'variant_name' => $variant->name
                ];
            }),
            'categories' => collect($categories)->unique('slug')->all(),
            'liked' => $this->whenLoaded('likes', fn() => $this->likes->count() ? true : false)
        ];
    }
}
