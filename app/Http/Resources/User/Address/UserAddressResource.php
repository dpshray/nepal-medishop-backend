<?php

namespace App\Http\Resources\User\Address;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return[
            'id'=>$this->id,
            'address'=>$this->address,
            'label' => $this->label,
            'longitude'=>$this->longitude,
            'latitude'=>$this->latitude,
        ];
    }
}
