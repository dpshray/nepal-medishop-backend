<?php

namespace App\Http\Resources\Admin\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminVendorProductDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $product = $this->product;
        return [
            'accepted' => (bool) $this->is_approved,
            'product_name' => $product->name,
            'product_detail' => $product->description,
            'prescription_required' => (bool)$product->prescription_required,
            "brand_name" => $product->brand->name,
            'variations' => $this->vendorPrices->map(function($item){
                return [
                    'variant_name' => $item->variation->name,
                    "size_value" => $item->variation->size_value,
                    "size_unit" => $item->variation->size_unit,
                    "units_in_stock" => (int) $item->units_in_stock,
                    "vendor_price" => (float) $item->price
                ];
            })
            
        ];
    }
}
