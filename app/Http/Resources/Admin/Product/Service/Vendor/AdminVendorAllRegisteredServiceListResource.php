<?php

namespace App\Http\Resources\Admin\Product\Service\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminVendorAllRegisteredServiceListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $is_approved_by_admin = is_int($this->is_approved) ? (bool)$this->is_approved : null;
        return [
            "is_approved_by_admin" => $is_approved_by_admin,
            "vendor_service_status" => (bool)$this->is_available,
            'vendor_name' => $this->vendor->user->name,
            'vendor_uuid' => $this->vendor->uuid,
            'service_name' => $this->service->name,
            'service_slug' => $this->service->slug,
            'vendor_service_price' => (float)$this->price,
        ];
    }
}
