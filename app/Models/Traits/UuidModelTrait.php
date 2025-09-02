<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait UuidModelTrait
{
    public static function bootUuidModelTrait()
    {
        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }
}
