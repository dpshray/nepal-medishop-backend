<?php

namespace App\Models;

use App\Enums\LoyalityPoint\LoyalityPointSourceEnum;
use App\Enums\LoyalityPoint\LoyalityPointStatusEnum;
use App\Enums\LoyalityPoint\LoyalityPointTypeEnum;
use Illuminate\Database\Eloquent\Model;

class LoyalityPoint extends Model
{
    const LOYALITY_POINTS = 1;


    protected $fillable = [
        'user_id',
        'order_id',
        'points',
        'type',
        'source',
        'description',
        'status',
        'balance_after',
    ];

    protected function casts(): array
    {
        return [
            'type' => LoyalityPointTypeEnum::class,
            'source' => LoyalityPointSourceEnum::class,
            'status' => LoyalityPointStatusEnum::class,
        ];
    }
}
