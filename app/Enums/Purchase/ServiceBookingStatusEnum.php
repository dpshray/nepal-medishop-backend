<?php

namespace App\Enums\Purchase;

enum ServiceBookingStatusEnum:string
{
    case PENDING = 'PENDING';
    case CONFIRMED = 'CONFIRMED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';
}
