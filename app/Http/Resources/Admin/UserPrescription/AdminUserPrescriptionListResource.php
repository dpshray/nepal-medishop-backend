<?php

namespace App\Http\Resources\Admin\UserPrescription;

use App\Models\UserPrescription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserPrescriptionListResource extends JsonResource
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
            'id' => $this->id,
            'description' => $this->description,
            'prescription_image' => $this->getFirstMediaUrl(UserPrescription::PRESCRIPTION_IMAGE),
            'created_at' => $this->created_at->toDateTimeString(),
            // 'updated_at' => $this->updated_at->toDateTimeString(),

            // Related user info
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'mobile_number' => $this->user->mobile_number ?? null,
            ],
        ];
    }
}
