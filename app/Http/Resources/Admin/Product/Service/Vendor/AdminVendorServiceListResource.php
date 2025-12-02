<?php

namespace App\Http\Resources\Admin\Product\Service\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminVendorServiceListResource extends JsonResource
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
            'is_approved_by_admin' => (bool)$this->pivot->is_approved,
            'vendor_service_status' => (bool)$this->pivot->is_available,
            'vendor_uuid' => $this->uuid,
            'vendor_name' => $this->user->name,
            'service_price' => (float)$this->pivot->price,
        ];
    }
}
