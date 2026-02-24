<?php

namespace App\Http\Resources\User\Product\Order;

use App\Http\Resources\User\Review\PackageReviewListResource;
use App\Http\Resources\User\Review\ProductReviewListResource;
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
            "previous_price" => (float)$this->previous_price,
            'promo_code' => $this->promoCode ? [
                'code' => $this->promoCode->code,
                'discount' => $this->promoCode->discount_percent,
            ] : null,
            'delivery_charge' => (float) $this->delivery_charge,
            'gift_wrap' => (bool) $this->gift_wrap,
            'gift_wrap_remarks' => $this->gift_wrap_remarks,
            'gift_wrap_charge' => (float) $this->gift_wrap_charge,
            "payment_method" => $this->payment_method,
            "payment_status" => $this->payment_status,
            "status" => $this->status,
            "created_at" => $this->created_at->format('Y/m/d'),
            'ordered_items' => $this->orderItems->map(function ($item) {
                $data = [
                    "Prescription_image" => $item->getFirstMediaUrl($item::PRESCRIPTION_IMAGE),
                    "image" => $item->image,
                    'item_name' => $item->item_name,
                    'item_slug' => $item->item_slug,
                    'variant_name' => $item->variant_name,
                    'variant_size' => $item->variant_size,
                    'quantity' =>  (int) $item->quantity,
                    'price' => (float) $item->price,
                    'subtotal' => (float) $item->total,
                    'my_reviews' => ($item->item_type == Product::class) ? ProductReviewListResource::collection($item->product->reviews) : PackageReviewListResource::collection($item->package->reviews)
                ];
                if ($item->item_type == Product::class) {
                    $data = [...['type' => 'product'], ...$data];
                } else {
                    $data = [...['type' => 'package'], ...$data];
                }
                return $data;
            })
        ];
    }
}
