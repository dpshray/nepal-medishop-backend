<?php

namespace App\Http\Resources\User\Product\Category;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientCategoryResource extends JsonResource
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
            'menu_order'=>$this->menu_order?'Active':'Inactive',
            'image' => $this->whenLoaded('media', fn() => $this->getFirstMediaUrl(Category::CATEGORY_IMAGE)),
        ];
    }
}
