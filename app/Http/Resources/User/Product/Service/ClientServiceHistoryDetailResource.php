<?php

namespace App\Http\Resources\User\Product\Service;

use App\Enums\Purchase\DiscountEnum;
use App\Enums\Purchase\ServiceBookingStatusEnum;
use App\Models\Product\Service\Service;
use App\Models\Product\Service\ServiceBooking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientServiceHistoryDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $status = $this->is_booking_expired ? ServiceBookingStatusEnum::EXPIRED : $this->status;
        $data = [
            'order_code' => $this->order_code,
            'order_status' => $status,
            'price' => (float)$this->price,
            'service_name' => $this->service->name,
            'service_image' => $this->service->getFirstMediaUrl(Service::SERVICE_MEDIA),
            'service_description' => $this->service->description,
            'test_requirements' => $this->service->test_requirements,
            'service_categories' => $this->service->categories->map(fn($category) => ['name' => $category->name]),
            'service_tags' => $this->service->tags->map(fn($tag) => ['name' => $tag->name]),
            'payment_method' => $this->payment_method,
            "name" => $this->name,
            "email" => $this->email,
            "mobile" => $this->mobile,
            "address" => $this->address,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
            "user_name" => $this->orderedBy->name,
            "message" => $this->message,
            "appointment_at" => $this->appointment_at->format('Y/m/d H:i:s'),
            "report_document" => $this->getFirstMediaUrl(ServiceBooking::SERVICE_BOOKING_REPORT),
            'coupon_code' => null,
            'coupon_code_discount_amount' => null,
            'service_discount_amount' => null
        ];
        if ($this->discounts->isNotEmpty()) {
            foreach ($this->discounts as $discount) {
                if ($discount->type == DiscountEnum::COUPON_DISCOUNT) {
                    $data['coupon_code'] = $discount->code;
                    $data['coupon_code_discount_amount'] = (float)$discount->discount_amount;
                }elseif ($discount->type == DiscountEnum::SERVICE_DISCOUNT) {
                    $data['service_discount_amount'] = (float)$discount->discount_amount;
                }
            }
        }
        return $data;
    }
}
