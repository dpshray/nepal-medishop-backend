<?php

namespace App\Enums\Purchase;

enum OrderStatusEnum: string
{
    case PENDING = 'PENDING';  
    case PARTIALLY_DELIVERED = 'PARTIALLY_DELIVERED';  
    case SHIPPED = 'SHIPPED';  
    case DELIVERED = 'DELIVERED';
    case CANCELLED = 'CANCELLED';
}
