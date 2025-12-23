<?php

namespace App\Http\Resources\Admin\Grievance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminGrievanceListResource extends JsonResource
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
            'submitted_by' => $this->user->name,
            "submitted_at" => $this->created_at->format('Y-m-d'),
        ];
    }
}
