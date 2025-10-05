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
        // return parent::toArray($request);
        /* $price = $this->price;
        $previous_price = null;
        if ($this->discount_price) {
            $previous_price = (float) $price;
            $price = (float) $this->discount_price;
        } */
        ['price' => $price, 'previous_price' => $previous_price] = $this->calculateDiscountPrice($this->price, $this->discount_percent);

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
