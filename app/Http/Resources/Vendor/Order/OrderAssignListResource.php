<?php

namespace App\Http\Resources\Vendor\Order;

use App\Enums\OrderUserTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderAssignListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $data = [
            'order_uuid' => $this->uuid,
            "payment_method" => $this->payment_method,
            "payment_status" => $this->payment_status,
            "status" => $this->status,
            'no_of_ordered_items' => $this->whenCounted('orderItems')
        ];
        if ($this->user_type == OrderUserTypeEnum::USER->value) {
            $data = [...$data, ...[
                'order_code' => $this->order_code,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'mobile' => $this->user->mobile_number,
                'address' => $this->address,
            ]];
        } elseif ($this->user_type == OrderUserTypeEnum::GUEST->value) {
            $data = [...$data, ...[
                'order_code' => $this->order_code,
                'name' => $this->name,
                'email' => $this->email,
                'mobile' => $this->mobile,
                'address' => $this->address,
            ]];
        }
        return $data;
    }
}
