<?php

namespace App\Http\Resources\Admin\Purchase\Kitbag;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminKitbagListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        /* 
              {
        "id": 7,
        "uuid": "dc0ea028-1f3c-465b-8cdc-b6d998512e76",
        "user_id": 1,
        "created_at": "2025-11-04 11:02:43",
        "user": {
          "id": 1,
          "status": 1,
          "uuid": "bf0e127e-594d-410e-9445-f3286270e356",
          "user_type": "1",
          "name": "admin",
          "email": "admin@gmail.com",
          "email_verified_at": "2025-11-03T16:42:01.000000Z",
          "mobile_number": "9820832722",
          "created_at": "2025-11-03T16:42:01.000000Z",
          "updated_at": "2025-11-03T16:42:01.000000Z",
          "deleted_at": null
        }
      }
        */
        return [
            'kitbag_uuid' => $this->uuid,
            'username' => $this->user->name,
            'email' => $this->user->email,
            'created_at' => $this->created_at->format('Y/m/d'),
            'no_of_kitbag_items' => $this->kitbag_items_count
        ];
    }
}
