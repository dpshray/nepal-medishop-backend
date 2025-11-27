<?php

namespace App\Http\Resources\User\UserPrescription;

use App\Models\UserPrescription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPrescriptionResource extends JsonResource
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
            'id'=>$this->id,
            'prescription_image'=>$this->getFirstMediaUrl(UserPrescription::PRESCRIPTION_IMAGE),
            'description'=>$this->description,
            'created_at'=>$this->created_at,
        ];
    }
}
