<?php

namespace App\Http\Resources\Admin\Notification;

use App\Enums\NotificationTypeEnum;
use App\Models\ProductVendor;
use App\Models\Purchase\Order;
use App\Notifications\AdminVendorProductStatusUpdateNotification;
use App\Notifications\UserOrderNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminNotificationDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $type = match ($this->type) {
            UserOrderNotification::class => NotificationTypeEnum::ORDER->value,
        };
        if ($type == NotificationTypeEnum::ORDER->value) {
            return $this->orderNotificationDetails($this);
        }
    }

    private function orderNotificationDetails($notification): array {
        $order_id = $notification->data['order_id'];
        $order = Order::with(['orderItems.item'])->findOrFail($order_id);
        $products = $order->orderItems->map(function($OI){
            return [
                'type' => class_basename($OI->item_type),
                'item_name' => $OI->item->name,
                'quantity' => (int)$OI->quantity,
                'item_variant_id' => $OI->variant_name,
                'price' => (float)$OI->price,
                'total' => (float)$OI->total
            ];
        });
        return [
            'order_code' => $order->order_code,
            'customer_name' => $order->customer_name,
            'email' => $order->mail,
            'phone' => $order->mob_no,
            'address' => $order->address,
            'gift_wrap' => (bool)$order->gift_wrap,
            'gift_wrap_remarks' => $order->gift_wrap_remarks,
            'gift_wrap_charge' => (float)$order->gift_wrap_charge,
            'latitude' => $order->latitude,
            'longitude' => $order->longitude,
            'description' => $order->description,
            'price' => (float)$order->price,
            'products' => $products
        ];
    }
}
