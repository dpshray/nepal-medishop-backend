<?php

namespace App\Http\Resources\User\Product\HealthCondition;

use App\Models\HealthCondition;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientHealthConditionListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "name" => $this->name,
            "slug" => $this->slug,
            "description" => $this->description,
            'image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl(HealthCondition::HEALTH_CONDITION_IMAGE)),
        ];
        // return parent::toArray($request);
    }
}
