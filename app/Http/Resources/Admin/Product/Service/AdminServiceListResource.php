<?php

namespace App\Http\Resources\Admin\Product\Service;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminServiceListResource extends JsonResource
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
            'is_active' => (bool)$this->is_active,
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => (float)$this->price 
        ];
    }
}
