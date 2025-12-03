<?php

namespace App\Http\Resources\Vendor\Product\Service;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorServiceListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "slug" => $this->slug,
            "admin_price" => (float)$this->price,
            "admin_discount_percent" => (float)$this->discount_percent,
        ];
    }
}
