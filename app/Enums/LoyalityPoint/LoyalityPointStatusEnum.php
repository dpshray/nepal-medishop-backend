<?php

namespace App\Enums\LoyalityPoint;

enum LoyalityPointStatusEnum: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case EXPIRED = 'EXPIRED';
    case CANCELLED = 'CANCELLED';
}
