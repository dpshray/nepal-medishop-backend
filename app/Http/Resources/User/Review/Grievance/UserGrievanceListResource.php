<?php

namespace App\Http\Resources\User\Review\Grievance;

use App\Models\Grievance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserGrievanceListResource extends JsonResource
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
            "subject" => $this->subject,
            "submitted_at" => $this->created_at->format('Y-m-d'),
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'images' => $this->getMedia(Grievance::GRIEVANCE_IMAGE)->map->getUrl(),
        ];
    }
}
