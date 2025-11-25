<?php

namespace App\Http\Resources\Admin\Vendor\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminVendorOrderAssignListResource extends JsonResource
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
            'vendor_uuid' => $this->uuid,
            "vendor_location" => $this->location,
            "store_name" => $this->store_name
        ];
    }
}
