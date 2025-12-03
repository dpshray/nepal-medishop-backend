<?php

namespace App\Http\Resources\Vendor\Product\Service;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorAssignedBookingSericeListResource extends JsonResource
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
            "booking_uuid" => $this->uuid,
            "status" => $this->status,
            "user_name" => $this->orderedBy->name,
            "service_name" => $this->service->name,
            "service_slug" => $this->service->slug,
            "message" => $this->message,
            "appointment_at" => $this->appointment_at->format('Y/m/d H:i:s'),
        ];
    }
}
