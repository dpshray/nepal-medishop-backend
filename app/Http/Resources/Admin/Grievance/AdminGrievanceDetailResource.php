<?php

namespace App\Http\Resources\Admin\Grievance;

use App\Models\Grievance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminGrievanceDetailResource extends JsonResource
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
            "uuid" => $this->uuid,
            "status" => $this->status,
            "name" => $this->name,
            "email" => $this->email,
            "phone" => $this->phone,
            "subject" => $this->subject,
            "detail" => $this->detail,
            'submitted_by' => $this->user->name,
            "created_at" => $this->created_at->format('Y/m/d'),
            'images' => $this->whenLoaded('media', fn() => $this->getMedia(Grievance::GRIEVANCE_IMAGE)->map(fn($item) => $item->getUrl())),
        ];
    }
}
