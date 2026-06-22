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
            'product_uuid' => $this->uuid,
            "product_name" => $this->name,
            'brand' => $this->whenLoaded('brand', fn() => $this->brand->name),
            'variations' => $this->whenLoaded('variations', function () {
                return $this->variations->map(fn($item) => [
                    'id' => $item->id,
                    'name' => $item->strength,
                    'form_type' => $item->form_type,
                    'package_type' => $item->package_type,
                    'package_size' => $item->package_size,
                    'size_value' => (float) $item->size_value,
                    'size_unit' => $item->size_unit,
                ]);
            })
        ];
    }
}
