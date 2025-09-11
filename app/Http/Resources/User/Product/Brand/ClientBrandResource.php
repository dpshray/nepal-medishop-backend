<?php

namespace App\Http\Resources\User\Product\Brand;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientBrandResource extends JsonResource
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
            'slug' => $this->slug,
            'name' => $this->name,
            'image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl(Brand::BRAND_IMAGE)),
            'is_featured' => $this->is_featured,
            'is_popular' => $this->is_popular,
        ];
    }
}
