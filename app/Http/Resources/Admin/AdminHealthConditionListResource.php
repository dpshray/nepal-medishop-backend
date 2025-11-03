<?php

namespace App\Http\Resources\Admin;

use App\Models\HealthCondition;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminHealthConditionListResource extends JsonResource
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
            "name" => $this->name,
            "slug" => $this->slug,
            "description" => $this->description,
            'image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl(HealthCondition::HEALTH_CONDITION_IMAGE)),
        ];
    }
}
