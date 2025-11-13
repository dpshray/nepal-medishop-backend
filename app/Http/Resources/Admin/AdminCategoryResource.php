<?php

namespace App\Http\Resources\Admin;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminCategoryResource extends JsonResource
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
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'menu_order'=>$this->menu_order,
            'image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl(Category::CATEGORY_IMAGE)),
            'discount_percent' => (float) $this->discount_percent
        ];
    }
}
