<?php

namespace App\Http\Resources\Admin\Product\Service\Booking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminServiceBookingDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $user = $this->orderedBy;
        $service = $this->service;
        $vendor = $this->assignedVendor;
        return [
            "status" => $this->status,
            "user" => $user ? [
                "name" => $user->name,
                "email" => $user->email,
            ]: null,
            "assigned_vendor" => $vendor ? [
                'name' => $vendor->user->name,
                'email' => $vendor->user->email
            ] : null,
            "service_name" => $service->name,
            "service_price" => (float)$service->price,
            "service_discount_percent" => (float)$service->discount_percent,
            "service_description" => $service->description,
            "test_requirements" => $service->test_requirements,
            "message" => $this->message,
            "appointment_at" => $this->appointment_at->format('Y/m/d H:i:s'),
            "service_created_at" => $this->created_at->format('Y/m/d'),
        ];
    }
}
