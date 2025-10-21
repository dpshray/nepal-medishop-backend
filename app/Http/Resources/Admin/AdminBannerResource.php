<?php

namespace App\Http\Resources\Admin;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminBannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return  [
            "uuid" => $this->uuid,
            "display_status" => (boolean) $this->display_status,
            "order" => (int) $this->order,
            "title" => $this->title,
            "url" => $this->url,
            'image' => $this->getFirstMediaUrl(Banner::BANNER_MEDIA)
        ];
    }
}
