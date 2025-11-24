<?php

namespace App\Http\Resources\Vendor\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorVariantBatchNumberListResource extends JsonResource
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
            "batch_number_id" => $this->id,
            "batch_number" => $this->batch_number,
            "stock_left" => $this->units_in_stock
        ];
    }
}
