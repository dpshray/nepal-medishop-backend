<?php

namespace App\Http\Resources\Admin\Product\Service\Booking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminServiceBookingListResource extends JsonResource
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
            "ordered_by" => $this->orderedBy->name,
            "service_name" => $this->service->name,
            "service_slug" => $this->service->slug,
            "assigned_vendor" => $this->assignedVendor?->user->name,
            "appointment_at" => $this->appointment_at->format('Y/m/d H:i:s'),
            "created_at" => $this->created_at->format('Y/m/d'),
        ];
    }
}
