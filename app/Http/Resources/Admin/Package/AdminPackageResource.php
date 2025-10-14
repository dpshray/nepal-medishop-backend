<?php

namespace App\Http\Resources\Admin\Package;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminPackageResource extends JsonResource
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
            'package_name'=>$this->name,
            'slug'=>$this->slug,
            'status'=>(bool)$this->status,
            'description'=>$this->description,
            'price'=>$this->price,
            'discount_percent'=>$this->discount_percent,
            'rating'=>$this->rating,
        ];
    }
}
