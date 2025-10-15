<?php

namespace App\Http\Resources\Admin\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorProductPriceListResource extends JsonResource
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
            'status' => (bool) $this->status,
            'vendor' => $this->whenLoaded('ProductVendor', function () {
                return [
                    'id' => $this->ProductVendor->id,
                    'name' => $this->ProductVendor->vendor->name,
                ];
            }),
            'product_variation' => $this->whenLoaded('variation', function () {
                return [
                    'id' => $this->variation->id,
                    'name' => $this->variation->name,
                    'product_name' => $this->variation->product->name ?? null,
                    'size_value' => (float) $this->variation->size_value,
                    'size_unit' => $this->variation->size_unit,
                ];
            }),
            'price' => (float) $this->price,
            'units_in_stock' => (int) $this->units_in_stock,
        ];
    }
}
