<?php

namespace App\Http\Resources\User\Promocode;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPromoCodeResource extends JsonResource
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
            'code'=>$this->code,
            'discount_percent'=> (float)$this->discount_percent,
            'start_date'=>$this->start_date,
            'end_date'=>$this->end_date,
            'description'=>$this->description,
            'is_active'=>(bool)$this->is_active,
        ];
    }
}
