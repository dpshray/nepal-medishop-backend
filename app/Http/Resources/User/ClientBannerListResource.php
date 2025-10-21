<?php

namespace App\Http\Resources\User;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientBannerListResource extends JsonResource
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
            "title" => $this->title,
            "url" => $this->url,
            'image' => $this->getFirstMediaUrl(Banner::BANNER_MEDIA)
        ];
    }
}
