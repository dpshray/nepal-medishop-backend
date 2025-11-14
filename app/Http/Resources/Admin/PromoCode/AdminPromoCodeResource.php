<?php

namespace App\Http\Resources\Admin\PromoCode;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminPromoCodeResource extends JsonResource
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
            'code'=>$this->code,
            'discount_percent'=>$this->discount_percent,
            'description'=>$this->description,
            'start_date'=>$this->start_date,
            'end_date'=>$this->end_date,
            'is_active'=>(bool)$this->is_active,
        ];
    }
}
