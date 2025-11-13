<?php

namespace App\Http\Resources\Admin\Vendor;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminVendorUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'mobile_number' => $this->mobile_number,
            'vendor_details' => $this->whenLoaded('vendor', fn() => [
                'is_verified' => (bool)!empty($this->email_verified_at),
                'store_name' => $this->vendor->store_name,
                'store_description' => $this->vendor->store_description,
                'location' => $this->vendor->location,
                'country' => $this->vendor->country,
                'state' => $this->vendor->state,
                'district' => $this->vendor->district,
                'municipality' => $this->vendor->municipality,
                'postal_code' => $this->vendor->postal_code,
                'bank_name' => $this->vendor->bank_name,
                'bank_account_holder_name' => $this->vendor->bank_account_holder_name,
                'bank_account_number' => $this->vendor->bank_account_number,
                'documents' => [
                    'citizenship_card' => $this->vendor->getMedia(Vendor::CITIZENSHIP_CARD)->map(fn($item) => $item->getFullUrl()),
                    'tax_certificate' => $this->vendor->getMedia(Vendor::TAX_CERTIFICATE)->map(fn($item) => $item->getFullUrl()),
                    'business_license' => $this->vendor->getMedia(Vendor::BUSINESS_LICENSE)->map(fn($item) => $item->getFullUrl())
                ]
            ])
        ];
    }
}


