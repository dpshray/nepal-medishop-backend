<?php

namespace App\Http\Resources\User;

use App\Enums\UserTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserLoginResource extends JsonResource
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
            "uuid" => $this->uuid,
            "name" => $this->name,
            "email" => $this->email,
            "user_type" => UserTypeEnum::tryFrom($this->user_type)?->name
        ];
    }
}
