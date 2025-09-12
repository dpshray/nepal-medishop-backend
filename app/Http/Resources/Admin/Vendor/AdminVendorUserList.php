<?php

namespace App\Http\Resources\Admin\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminVendorUserList extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'user_uuid' => $this->uuid,
            'name' => $this->name,
            'mobile_number' => $this->mobile_number,
            'store_name' => $this->whenLoaded('vendor', fn() => $this->vendor->store_name),
        ];
    }
}
