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
            "product_name" => $this->whenLoaded('product', fn() => $this->product->name),
            'product_uuid' => $this->whenLoaded('product', fn() => $this->product->uuid),
            'brand' => $this->whenLoaded('product', fn() => $this->product->brand->name),
            'variations' => $this->whenLoaded('product', function(){
                return $this->product->variations->map(fn($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'size_value' => (float) $item->size_value,
                    'size_unit' => $item->size_unit,
                ]);
            })
        ];
    }
}
