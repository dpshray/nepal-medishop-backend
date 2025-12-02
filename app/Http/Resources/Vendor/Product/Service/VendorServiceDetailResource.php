<?php

namespace App\Http\Resources\Vendor\Product\Service;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorServiceDetailResource extends JsonResource
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
            $vendor_price = (float)$vendor->pivot->price;
            $vendor_service_status = (bool)$vendor->pivot->is_available;
            $is_approved_by_admin = (bool)$vendor->pivot->is_approved;
        }
        return [
            "service_id" => $this->id,
            "is_made_available_by_admin" => (bool)$this->is_active,
            "is_approved_by_admin" => (bool)$is_approved_by_admin,
            "is_vendor_already_applied" => (bool) ($vendor),
            'vendor_service_status' => (bool)$vendor_service_status,
            "service_name" => $this->name,
            "service_slug" => $this->slug,
            "admin_price" => (float)$this->price,
            "admin_discount_percent" => (float)$this->discount_percent,
            'added_by_admin_at' => $this->created_at->format('Y/m/d'),
            'vendor_price' => $vendor_price,
        ];
    }
}
