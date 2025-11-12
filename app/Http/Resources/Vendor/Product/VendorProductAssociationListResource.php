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
            'mobile_number' => $this->associatedVendor->user->mobile_number
        ];
    }
}
