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
            "order_uuid" => $this->uuid,
            'order_code' => $this->order_code,
            'customer_name' => $this->customer_name,
            "order_status" => $this->status,
            "delivery_address" => $this->address,
            "mobile" => $this->mob_no,
            "email" => $this->mail,
            "order_items_count" => (int) $this->order_items_count
        ];
    }
}
