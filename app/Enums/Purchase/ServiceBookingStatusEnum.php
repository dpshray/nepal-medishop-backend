<?php

namespace App\Enums\Purchase;

enum ServiceBookingStatusEnum:string
{
    case PENDING = 'PENDING';
    case CONFIRMED = 'CONFIRMED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';

    public static function exceptPending(): array
    {
        return array_map(
            fn($case) => $case->value,
            array_filter(self::cases(), fn($case) => $case !== self::PENDING)
        );
    }
}
