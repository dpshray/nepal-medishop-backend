<?php

namespace App\Http\Resources\Vendor\Product\Service;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorRegisteredServiceListResource extends JsonResource
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
            'vendor_service_status' => (bool) $this->pivot->is_available,
            'service_name' => $this->name,
            'service_slug' => $this->slug,
            'vendor_price' => (float)$this->pivot->price
        ];
    }
}
