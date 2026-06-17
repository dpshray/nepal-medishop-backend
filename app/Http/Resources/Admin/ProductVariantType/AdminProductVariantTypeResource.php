<?php

namespace App\Http\Resources\Admin\ProductVariantType;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminProductVariantTypeResource extends JsonResource
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
            'id'   => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,

            'package_types' => $this->packageTypes->map(function ($package_type) {
                return [
                    'id'   => $package_type->id,
                    'uuid' => $package_type->uuid,
                    'name' => $package_type->name,

                    'unit_types' => $package_type->unitTypes->map(function ($unit_type) {
                        return [
                            'id'   => $unit_type->id,
                            'uuid' => $unit_type->uuid,
                            'name' => $unit_type->name,
                        ];
                    }),
                ];
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
