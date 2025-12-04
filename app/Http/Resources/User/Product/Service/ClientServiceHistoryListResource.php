<?php

namespace App\Http\Resources\User\Product\Service;

use App\Enums\Purchase\ServiceBookingStatusEnum;
use App\Models\Product\Service\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientServiceHistoryListResource extends JsonResource
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
        return [
            "booking_uuid" => $this->uuid,
            "status" => $status,
            "order_code" => $this->order_code,
            "client_name" => $this->name,
            "service_name" => $this->service->name,
            "image" => $this->service->getFirstMediaUrl(Service::SERVICE_MEDIA),
            "price" => (float)$this->price,
            "appointment_at" => $this->appointment_at->format('Y/m/d H:i:s')
        ];
    }
}
