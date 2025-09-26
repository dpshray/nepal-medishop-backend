<?php

namespace App\Http\Resources\Admin\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminVendorProductListResource extends JsonResource
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
            'vendor_status' => (boolean)$this->status,
            'approved' => (boolean)$this->is_approved,
            'product_name' => $this->whenLoaded('product', $this->product->name), 
            'product_variants' => $this->whenLoaded('vendorPrices', function(){
                return $this->vendorPrices->map(fn($item) => [
                    "price" => (float) $item->price,
                    "units_in_stock" => $item->units_in_stock,
                    "variation_name" => $item->variation->name,
                    "variation_size_value" => (float) $item->variation->size_value,
                    "variation_size_unit" => $item->variation->size_unit

                ]);
            })
        ];
    }
}
