<?php

namespace App\Http\Resources\User\Product\Service;

use App\Models\Product\Service\Service;
use App\Traits\HelperTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientServiceListResource extends JsonResource
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
        ['price' => $price, 'previous_price' => $previous_price] = $this->calculateDiscountPrice($this->price, $this->discount_percent);
        return [
            "image" => $this->getFirstMediaUrl(Service::SERVICE_MEDIA),
            "name" => $this->name,
            "slug" => $this->slug,
            "price" => $price,
            "previous_price" => $previous_price,
        ];
    }
}
