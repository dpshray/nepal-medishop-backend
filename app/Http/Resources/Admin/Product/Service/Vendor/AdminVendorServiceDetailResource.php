<?php

namespace App\Http\Resources\Admin\Product\Service\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminVendorServiceDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $vendor = $vendor_price = $vendor_service_status = $is_approved_by_admin = null;
        if (count($this->vendors)) {
            $vendor = $this->vendors->first();
            $vendor_price = $vendor->pivot->price;
            $vendor_service_status = (bool)$vendor->pivot->is_available;
            $is_approved_by_admin = (bool)$vendor->pivot->is_approved;
        }
        return [
            "is_approved_by_admin" => $is_approved_by_admin,
            "vendor_service_status" => $vendor_service_status,
            'service_name' => $this->name,
            'service_slug' => $this->slug,
            'service_description' => $this->description,
            "test_requirements" => $this->test_requirements,
            "admin_price" => (float)$this->price,
            "discount_percent" => (float)$this->discount_percent,
            "vendor_price" => (float)$vendor_price,
            'vendor_detail' => $vendor ? [
                'vendor_name' => $vendor->user->name,
                'store_name' => $vendor->store_name,
                "email" => $vendor->user->email,
                "email_verified_at" => $vendor->user->email_verified_at,
                "mobile_number" => $vendor->user->mobile_number,
            ] : null
        ];
    }
}
