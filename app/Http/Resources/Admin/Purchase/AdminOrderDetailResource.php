<?php

namespace App\Http\Resources\Admin\Purchase;

use App\Enums\OrderUserTypeEnum;
use App\Enums\UserTypeEnum;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderDetailResource extends JsonResource
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
            "order_code" => $this->order_code,
            'user_type' => $this->user_type,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            "address" => $this->address,
            "description" => $this->description,
            "price" => (float) $this->price,
            "payment_method" => $this->payment_method,
            "payment_status" => $this->payment_status,
            "status" => $this->status,
            "created_at" => $this->created_at->format('Y/m/d'),
            'ordered_items' => $this->orderItems->map(function ($item) {
                $data = [
                    'item_name' => $item->item_name,
                    'variant_name' => $item->variant_name,
                    'variant_size' => $item->variant_size,
                    'quantity' =>  $item->quantity,
                    'price' => (float) $item->price,
                    'subtotal' => (float) $item->total
                ];
                if ($item->item_type == Product::class) {
                    $data = [...['type' => 'product'], ...$data];
                } else {
                    $data = [...['type' => 'package'], ...$data];
                }
                return $data;
            })
        ];
        if ($this->user_type == OrderUserTypeEnum::USER->value) {
            $user = $this->user;
            $data = [...$data, ...[
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->mobile_number
            ]];
        }
        return $data;
    }
}
