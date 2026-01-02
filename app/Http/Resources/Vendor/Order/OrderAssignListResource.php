<?php

namespace App\Http\Resources\Vendor\Order;

use App\Enums\OrderUserTypeEnum;
use App\Enums\Purchase\OrderItemStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        $vendor_id = Auth::user()->vendor->id;
        $vendor_assigned_items = $this->orderItems->where('assigned_vendor_id', $vendor_id);
        $vendor_assigned_items_count = $vendor_assigned_items->count();

        $order_item_status = OrderItemStatusEnum::ASSIGNED->value;
        $order_item_not_completely_batched = $vendor_assigned_items->where('status', OrderItemStatusEnum::ASSIGNED->value)->count() != $vendor_assigned_items_count; 
        $is_all_order_item_processing = $vendor_assigned_items->where('status', OrderItemStatusEnum::PROCESSING->value)->count() == $vendor_assigned_items_count;
        $is_all_item_delivered = $vendor_assigned_items->where('status', OrderItemStatusEnum::DELIVERED->value)->count() == $vendor_assigned_items_count;
        
        /* Log::info([
            $vendor_id,
            $vendor_assigned_items,
            $this->orderItems->where('assigned_vendor_id', $vendor_id)->where('status', OrderItemStatusEnum::ASSIGNED->value)->count() , 
            $vendor_assigned_items_count
        ]); */
        if ($is_all_order_item_processing) {
            $order_item_status = OrderItemStatusEnum::PROCESSING->value;
        }else if ($is_all_item_delivered) {
            $order_item_status = OrderItemStatusEnum::DELIVERED->value;
        }
        return [
            "order_uuid" => $this->uuid,
            'order_code' => $this->order_code,
            'customer_name' => $this->customer_name,
            "order_item_status" => $order_item_status == OrderItemStatusEnum::ASSIGNED->value ? 'PENDING' : $order_item_status, #overall order item status
            'order_status' => $this->status,
            "delivery_address" => $this->address,
            "mobile" => $this->mob_no,
            "email" => $this->mail,
            "gift_wrap" => $this->gift_wrap,
            'price' => (float)round($this->orderItems->where('assigned_vendor_id', Auth::user()->vendor->id)->sum('total'),2),
            "order_items_count" => (int) $this->order_items_count
        ];
    }
}
