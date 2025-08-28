<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name'=>$this->name,
            'mobile_number'=>$this->mobile_number,
            'email'=>$this->email,
            'is_admin'=>$this->is_admin,
            'email_verified_at'=>$this->email_verified_at,
        ];
    }
}
