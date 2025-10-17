<?php

namespace App\Enums\Purchase;

enum PaymentStatusEnum: string
{
    case INITIATED = 'INITIATED'; 
    case PENDING = 'PENDING'; 
    case PAID = 'PAID';  
    case UNPAID = 'UNPAID'; 
    case FAILED = 'FAILED';
    case CANCELLED = 'CANCELLED';

    public static function paymentUpdateValues(): array
    {
        return collect(self::cases())
            ->reject(fn($case) => in_array($case, [self::INITIATED, self::FAILED]))
            ->map(fn($case) => strtolower($case->value))
            ->values()
            ->all();
    }
}
