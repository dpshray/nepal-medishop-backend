<?php

namespace App\Http\Resources\Vendor\Order;

use App\Enums\OrderUserTypeEnum;
use App\Models\Product;
use App\Models\Purchase\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderAssignDetailResource extends JsonResource
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
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
            "description" => $this->description,
            "price" => (float) $this->price,
            "payment_method" => $this->payment_method,
            "payment_status" => $this->payment_status,
            "status" => $this->status,
            "created_at" => $this->created_at->format('Y/m/d'),
            'ordered_items' => $this->orderItems->map(function ($order_item) {
                $is_prescription_required = (bool) $order_item->product->prescription_required;
                $data = [
                    "order_item_id" => $order_item->id,
                    // 'item_name' => $order_item->item_name,
                    // 'variant_name' => $order_item->variant_name,
                    // 'variant_size' => $order_item->variant_size,
                    'quantity' =>  $order_item->quantity,
                    'price' => (float) $order_item->price,
                    'subtotal' => (float) $order_item->total
                ];
                if ($order_item->item_type == Product::class) {
                    $data = [...[
                        'type' => 'product',
                        'prescription_required' => $is_prescription_required,
                        'prescription_image' => $is_prescription_required ? $order_item->getFirstMediaUrl(OrderItem::PRESCRIPTION_IMAGE) : null,
                        'item_products' => [
                            [
                                'OIP_ID' => $order_item->orderItemProducts->firstWhere('product_variation_id', $order_item->item_variant_id)->id,
                                'variant_name' => $order_item->variant_name,
                                'variant_id' => (int) $order_item->item_variant_id,
                                'product_name' => $order_item->item_name,
                                'quantity' => $order_item->quantity,
                            ]
                        ]
                    ], ...$data];
                } else {
                    $data = [...[
                        'type' => 'package',
                        'prescription_required' => (bool) false,
                        'prescription_image' => null,
                        'item_products' => $order_item->item->packageProducts->flatMap(fn($PP) => [
                            [
                                'OIP_ID' => $order_item->orderItemProducts->firstWhere('product_variation_id', $PP->product_variation_id)->id,
                                'variant_name' => $PP->variant->name,
                                'variant_id' => (int)$PP->product_variation_id,
                                'product_name' => $PP->variant->product->name,
                                'quantity' => $PP['quantity'] * $order_item->quantity,
                            ]
                        ])
                    ], ...$data];
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
