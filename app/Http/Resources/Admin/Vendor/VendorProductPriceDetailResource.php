<?php

namespace App\Http\Resources\Admin\Vendor;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorProductPriceDetailResource extends JsonResource
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
            'id' => $this->id,
            'status' => $this->status !== null ? (bool) $this->status : null,
            'vendor' => $this->whenLoaded('ProductVendor', function () {
                return [
                    'id' => $this->ProductVendor->id,
                    'name' => $this->ProductVendor->vendor->name,
                    'email'=>$this->ProductVendor->vendor->email,
                ];
            }),
            'product_variation' => $this->whenLoaded('variation', function () {
                return [
                    'id' => $this->variation->id,
                    'variation_name' => $this->variation->name,
                    'product_name' => $this->variation->product->name ?? null,
                    'size_value' => (float) $this->variation->size_value,
                    'size_unit' => $this->variation->size_unit,
                    'product_image' => $this->variation->product?->getFirstMedia(Product::PRODUCT_FEATURE)?->getUrl(),

                ];
            }),
            'price' => (float) $this->price,
            'units_in_stock' => (int) $this->units_in_stock,
        ];
    }
}
