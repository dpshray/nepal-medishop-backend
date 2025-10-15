<?php

namespace App\Http\Resources\User\Product;

use App\Models\Package;
use App\Models\Product;
use App\Traits\HelperTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserWishlistResource extends JsonResource
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
        if ($this->wishable_type == Product::class) {
            $product = $this->product;
            $item = $product->cheapestVariation;
    
            ['price' => $price, 'previous_price' => $previous_price] = $this->calculateDiscountPrice($item->platform_price, $product->discount_percent);
    
            $data = [
                'type' => strtolower(class_basename($this->wishable_type)),
                'name' => $product->name,
                'slug' => $product->slug,
                'rating' => (float) $product->rating,
                'brand' => $product->brand->name,
                'price' => $price,
                'previous_price' => $previous_price,
                'feature_image' => $product->getFirstMediaUrl(Product::PRODUCT_FEATURE),
                'liked' => $product->likes->count() ? true : false
            ];
        }else{
            $package = $this->package;
            ['price' => $price, 'previous_price' => $previous_price] = $this->calculateDiscountPrice($package->price, $package->discount_percent);

            $data = [
                'type' => strtolower(class_basename($this->wishable_type)),
                'name' => $package->name,
                'slug' => $package->slug,
                'rating' => (float) $package->rating,
                'brand' => null,
                'price' => $price,
                'previous_price' => $previous_price,
                'feature_image' => $this->package->getFirstMediaUrl(Package::PACKAGE_FEATURED),
                'liked' => $package->likes->count() ? true : false
            ];
        }
        return $data;
    }
}
