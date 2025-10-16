<?php

namespace App\Http\Resources\Admin\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserListResource extends JsonResource
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
            'uuid'=>$this->uuid,
            'name'=>$this->name,
            'email'=>$this->email,
            'mobile_number'=>$this->moblie_number,
            'email_verified'=>$this->email_verified_at?'Verified':'Not Verified',
            'Profile_image' => $this->getFirstMediaUrl(User::USER_PROFILE) ?: null,
            'total_orders' => (int) $this->total_orders,
            'total_items_purchased' => (int) ($this->total_items_purchased ?? 0),
            'total_purchase_amount' => (float) ($this->total_purchase_amount ?? 0),
        ];
    }
}
