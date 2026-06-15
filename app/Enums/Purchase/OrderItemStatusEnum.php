<?php

namespace App\Enums\Purchase;

enum OrderItemStatusEnum: string
{
    case PENDING = 'PENDING';
    case ASSIGNED = 'ASSIGNED';
    case CANCELLED = 'CANCELLED';
    case PROCESSING = 'PROCESSING';
    case DELIVERED = 'DELIVERED';
    case NOT_COMPLETELY_BATCHED = 'NOT_COMPLETELY_BATCHED';
    case RETURNED = 'RETURNED';
}
