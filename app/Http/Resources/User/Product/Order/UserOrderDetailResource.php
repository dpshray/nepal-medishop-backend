<?php

namespace App\Http\Resources\User\Product\Order;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserOrderDetailResource extends JsonResource
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
            "order_code" => $this->order_code,
            "address" => $this->address,
            "description" => $this->description,
            "price" => (float) $this->price,
            "payment_method" => $this->payment_method,
            "payment_status" => $this->payment_status,
            "status" => $this->status,
            "created_at" => $this->created_at->format('Y/m/d'),
            'ordered_items' => $this->orderItems->map(function($item){
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
                }else{
                    $data = [...['type' => 'package'], ...$data];
                }
                return $data;
            })
        ];
    }
}
