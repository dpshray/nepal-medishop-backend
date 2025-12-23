<?php

namespace App\Http\Resources\User\Notification;

use App\Enums\NotificationTypeEnum;
use App\Notifications\SavePushNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserNotificationListResource extends JsonResource
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
            SavePushNotification::class => NotificationTypeEnum::PUSH_NOTIFICATION->value,
        };
        return [
            'uuid' => $this->id,
            'subject' => $data['title'].' : '.$data['body'],
            'date' => $this->created_at->format('Y/m/d'),
            'read_at' => $this->read_at ? $this->read_at->format('Y/m/d') : null,
            'type' => $type
        ];
    }
}
