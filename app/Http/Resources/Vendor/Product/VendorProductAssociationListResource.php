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
            'product_name' => $this->product->name,
            'is_approved' => (bool)$this->is_approved,
            'approval_status' => $this->is_approved == 1 ? 'Accepted' : 'Rejected',
            'approval_date' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
            'variation' => $this->vendorPrices->map(function ($price) {
                return [
                    'id' => $price->id,

                    // PRODUCT INFO
                    'variant_name' => $price->variation->name ?? null,

                    // PRICE DETAILS
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
