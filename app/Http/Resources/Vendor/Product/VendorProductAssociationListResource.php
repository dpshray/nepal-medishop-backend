<?php

namespace App\Http\Resources\Vendor\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorProductAssociationListResource extends JsonResource
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
            'vendor_uuid' => $this->associatedVendor->uuid,
            'vendor_name' => $this->associatedVendor->user->name,
            'store_name' => $this->associatedVendor->store_name,
            'mobile_number' => $this->associatedVendor->user->mobile_number,
            'prices' => $this->vendorPrices->map(function ($price) {
                return [
                    'id' => $price->id,
                    'product_variation_id' => $price->product_variation_id,
                    'price' => $price->price,
                    'units_in_stock' => $price->units_in_stock,
                    'batch_number' => $price->batch_number,
                    'manufacture' => $price->manufacture,
                    'expiry_date' => $price->expiry_date,
                ];
            }),
        ];
    }
}
