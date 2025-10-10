<?php

namespace App\Http\Resources\Admin\Package;

use App\Models\Package;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminPackageDetailResource extends JsonResource
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
            'id'                => $this->id,
            'name'              => $this->name,
            'description'       => $this->description,
            'price'             => $this->price,
            'discount_percent'  => $this->discount_percent,
            'status'            => $this->status ? 'Active' : 'Inactive',
            'start_timestamps'  => $this->start_timestamps,
            'end_timestamps'    => $this->end_timestamps,

            'featured_image' => $this->whenLoaded('media', fn() => $this->getFirstMedia(Package::PACKAGE_FEATURED)->getUrl()),
            'gallery_images' => $this->whenLoaded('media', fn() => $this->getMedia(Package::PACKAGE_GALLERY)->map(fn($item) => $item->getUrl())),

            // Products inside the package
            'products' => $this->packageProducts->map(function ($packageProduct) {
                $variant = $packageProduct->variant;
                $product = $variant?->product;

                return [
                    'id'               => $packageProduct->id,
                    'quantity'         => $packageProduct->quantity,
                    'variant_id'       => $variant?->id,
                    'variant_name'     => $variant?->name,
                    'product_id'       => $product?->id,
                    'product_name'     => $product?->name,
                    'brand'            => $product?->brand?->name,
                    'categories'       => $product?->categories->pluck('name'),
                    'product_media'    => $variant->product->getFirstMedia(Product::PRODUCT_FEATURE)->getUrl(),
                ];
            }),
        ];
    }
}
