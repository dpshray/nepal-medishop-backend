<?php

namespace App\Http\Resources\Admin\Menu;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminCategoryMenuListResource extends JsonResource
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
            'menu_order' => $this->menu_order,
            'slug' => $this->slug,
            'name' => $this->name
        ];
    }
}
