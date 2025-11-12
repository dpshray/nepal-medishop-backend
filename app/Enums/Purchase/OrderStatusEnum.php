<?php

namespace App\Enums\Purchase;

enum OrderStatusEnum: string
{
    case PENDING = 'PENDING';  
    case SHIPPED = 'SHIPPED';  
    case DELIVERED = 'DELIVERED';
    case CANCELLED = 'CANCELLED';
}
