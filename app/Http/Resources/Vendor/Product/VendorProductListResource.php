<?php

namespace App\Http\Resources\Vendor\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorProductListResource extends JsonResource
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
            "status" => (boolean) $this->status,
            "is_approved_by_admin" => (boolean) $this->is_approved,
            "name" => $this->whenLoaded('product', fn() => $this->product->name),
            'uuid' => $this->whenLoaded('product', fn() => $this->product->uuid),
            "brand" => $this->product->brand->name,
            "views_count" => $this->whenLoaded('product', fn() => $this->product->views_count),
            "total_units_in_stock" => (int) $this->units_in_stock_sum,
            'rating' => (float) $this->whenLoaded('product', fn() => $this->product->rating)
        ];
    }
}
