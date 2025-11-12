<?php

namespace App\Http\Resources\Vendor\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorStockedProductListResource extends JsonResource
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
            'accepted' => (bool) $this->status,
            'product_uuid' => $this->whenLoaded('product', fn() => $this->product->uuid) ,
            "product_name" => $this->whenLoaded('product', fn() => $this->product->name) ,
            'brand' => $this->product->brand->name,
            'variations' => $this->whenLoaded('vendorPrices', function () {
                return $this->vendorPrices->map(fn($item) => [
                    // "accepted" => (bool) $item->status,
                    "product_variation_id" => $item->variation->id,
                    "vendor_price" => (float) $item->price,
                    "units_in_stock" => (int) $item->units_in_stock,
                    "variant_name" => $item->variation->name,
                    "variant_size_value" => $item->variation->size_value,
                    "variant_size_unit" => $item->variation->size_unit,
                ]);
            })
        ];
    }
}
