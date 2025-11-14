<?php

namespace App\Http\Resources\User\Profile;

use App\Enums\UserTypeEnum;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
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
            'name'=>$this->name,
            'email'=>$this->email,
            'status'=>$this->status?'Active':'InActive',
            "user_type" => UserTypeEnum::tryFrom($this->user_type)?->name,
            'mobile_number'=>$this->mobile_number,
            'image' => $this->getFirstMediaUrl(User::USER_PROFILE) ?: null,
            'loyality_points' => round((($this->latestApprovedLoyalityPoints?->balance_after ?? 0) / 500), 2),
        ];
    }
}
