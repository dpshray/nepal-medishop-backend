<?php

namespace App\Http\Resources\Vendor\Notification;

use App\Enums\NotificationTypeEnum;
use App\Notifications\VendorProductStatusUpdateNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorNotificationListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $data = $this->data;
        $type = match ($this->type) {
            VendorProductStatusUpdateNotification::class => NotificationTypeEnum::VendorProductApproval->value,
        };
        return [
            'uuid' => $this->id,
            'subject' => $data['subject'],
            'date' => $this->created_at->format('Y/m/d'),
            'read_at' => $this->read_at ? $this->read_at->format('Y/m/d') : null,
            'type' => $type
        ];
    }
}
