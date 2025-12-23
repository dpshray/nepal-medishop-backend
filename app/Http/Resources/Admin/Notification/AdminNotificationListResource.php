<?php

namespace App\Http\Resources\Admin\Notification;

use App\Notifications\UserOrderNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminNotificationListResource extends JsonResource
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
            UserOrderNotification::class => 'Order'
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
