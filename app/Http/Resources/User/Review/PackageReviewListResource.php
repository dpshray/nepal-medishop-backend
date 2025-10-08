<?php

namespace App\Http\Resources\User\Review;

use App\Enums\UserTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageReviewListResource extends JsonResource
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
            'comment_uuid' => $this->uuid,
            'user_name' => $this->whenLoaded('user', fn() => $this->user->name),
            'review' => $this->review,
            'rating' => (float) $this->rating,
            'user_type' => $this->whenLoaded('user', fn() => [
                'user_type' => (int) $this->user->user_type,
                'label' => UserTypeEnum::from($this->user->user_type)->name
            ]),
            'review_date' => $this->created_at->format('d M Y'),
            'is_review_edited' => $this->updated_at->gt($this->created_at) ? true : false
        ];
    }
}
