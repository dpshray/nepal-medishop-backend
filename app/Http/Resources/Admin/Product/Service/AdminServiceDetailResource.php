<?php

namespace App\Http\Resources\Admin\Product\Service;

use App\Models\Product\Service\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminServiceDetailResource extends JsonResource
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
            "is_active" => (bool)$this->is_active,
            "name" => $this->name,
            "slug" => $this->slug,
            "description" => $this->description,
            "test_requirements" => $this->test_requirements,
            "price" => (float)$this->price,
            "created_at" => $this->created_at->format('Y/m/d'),
            "categories" => $this->categories->map(fn($category) => ['id' => $category->id, 'name' => $category->name]),
            "tags" => $this->tags->map(fn($tag) => ['id' => $tag->id, 'name' => $tag->name]),
            "image" => $this->getFirstMediaUrl(Service::SERVICE_MEDIA)
          ];
    }
}
