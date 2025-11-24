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
            'status' => $this->is_approved !== null ? (bool) $this->is_approved : null,
            'product_uuid' => $this->uuid,
            'product_name' => $this->product->name,
            'vendor' => $this->whenLoaded('vendor', function () {
                return [
                    'id' => $this->vendor->id,
                    'name' => $this->vendor->user->name,
                ];
            }),
            /* 'product_variation' => $this->whenLoaded('variation', function () {
                return [
                    'id' => $this->variation->id,
                    'variation_name' => $this->variation->name,
                    'product_name' => $this->variation->product->name ?? null,
                    'size_value' => (float) $this->variation->size_value,
                    'size_unit' => $this->variation->size_unit,
                ];
            }),
            'price' => (float) $this->price, */
            'units_in_stock' => (int) $this->vendorPrices->sum('units_in_stock'),
        ];
    }
}
