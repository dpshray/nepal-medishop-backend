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
        $order_item_status = null;
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
            "delivery_charge" => (float) $this->delivery_charge,
            "status" => $this->status,
            "ncm_order" => $this->ncmOrder ? [
                'id' => $this->ncmOrder->id,
                'uuid' => $this->ncmOrder->uuid,
                'order_id' => $this->ncmOrder->order_id,
                "ncm_order_id" => $this->ncmOrder->ncm_order_id,
                "fbranch" => $this->ncmOrder->fbranch,
                "tbranch" => $this->ncmOrder->tbranch,
                "package" => $this->ncmOrder->package,
                "weight" => $this->ncmOrder->weight,
                "cod_charge" => $this->ncmOrder->cod_charge,
                "delivery_charge" => $this->ncmOrder->delivery_charge,
                "instruction" => $this->ncmOrder->instruction,
                "delivery_status" => $this->ncmOrder->delivery_status,
                "delivery_type" => $this->ncmOrder->delivery_type,
                "created_at" => $this->ncmOrder->created_at,
            ] : null,
            'order_item_status' => '',
            "created_at" => $this->created_at->format('Y/m/d'),
            'ordered_items' => $this->orderItems->map(function ($order_item) use (&$order_item_status) {
                $order_item_status = $order_item->status;
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
                    $is_prescription_required = (bool) $order_item->item->prescription_required;
                    $data = [...[
                        'type' => 'product',
                        'prescription_required' => $is_prescription_required,
                        'prescription_image' => $is_prescription_required ? $order_item->getFirstMediaUrl(OrderItem::PRESCRIPTION_IMAGE) : null,
                        'item_products' => [
                            [
                                'OIP_ID' => $order_item->orderItemProducts->firstWhere('product_variation_id', $order_item->item_variant_id)->id,
                                'variant_name' => $order_item->variant_name,
                                'product_name' => $order_item->item_name,
                                'required_quantity' => $order_item->orderItemProducts->firstWhere('product_variation_id', $order_item->item_variant_id)->quantity,
                                'variant_id' => (int) $order_item->item_variant_id,
                                'assigned_batch_numbers' => ($bn = $order_item->orderItemProducts->flatMap(function ($item) {
                                    return $item->batchNumbers->map(fn($itm) => [
                                        'variant_id' => $itm->vendorProductPrice->variation->id,
                                        'batch_number' => $itm->vendorProductPrice->batch_number,
                                        'quantity' => $itm->quantity
                                    ]);
                                }))->isNotEmpty() ? $bn : null,
                                'batch_numbers' => $order_item->productVariant->vendorProductPrices->map(fn($vpp) => [
                                    'batch_number_id' => (int)$vpp->id,
                                    'quantity' => (int)$vpp->stock_left,
                                    'batch_number' => $vpp->batch_number
                                ])
                            ]
                        ]
                    ], ...$data];
                } else {
                    $data = [
                        ...[
                            'type' => 'package',
                            'prescription_required' => false,
                            'prescription_image' => null,
                            'item_products' => $order_item->orderItemProducts->map(function ($item) {
                                return [
                                    'OIP_ID' => $item->id,
                                    'variant_id' => $item->product_variation_id,
                                    'product_name' => $item->variation->product->name,
                                    'variant_name' => $item->variation->name,
                                    'required_quantity' => $item->quantity,
                                    'assigned_batch_numbers' => $item->batchNumbers->isEmpty() ? null : $item->batchNumbers->map(fn($i) => [
                                        'variant_id' => $item->variation->id,
                                        'batch_number' => $i->vendorProductPrice->batch_number,
                                        'quantity' => $i->quantity
                                    ]),
                                    'batch_numbers' => $item->variation->vendorProductPrices->map(fn($vpp) => [
                                        'batch_number_id' => (int)$vpp->id,
                                        'quantity' => (int)$vpp->stock_left,
                                        'batch_number' => $vpp->batch_number
                                    ])
                                ];
                            })
                        ],
                        ...$data
                    ];
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
        $data['price'] = collect($data['ordered_items'])->sum('subtotal');
        $data['order_item_status'] = $order_item_status;
        return $data;
    }
}
