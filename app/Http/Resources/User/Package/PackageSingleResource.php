<?php

namespace App\Http\Resources\User\Package;

use App\Models\Package;
use App\Traits\HelperTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageSingleResource extends JsonResource
{
    use HelperTrait;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        ['price' => $price, 'previous_price' => $previous_price] = $this->original_price;
        
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => $price,
            'previous_price' => $previous_price,
            'rating' => (float) $this->rating,
            'image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl(Package::PACKAGE_FEATURED)),
            'liked' => $this->whenLoaded('likes', fn() => $this->likes->count() ? true : false)
        ];
    }
}
