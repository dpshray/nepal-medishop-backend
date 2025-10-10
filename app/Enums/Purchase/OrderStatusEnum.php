<?php

namespace App\Enums\Purchase;

enum OrderStatusEnum: string
{
    case INITIATED = 'INITIATED';  
    case PENDING = 'PENDING';  
    case SHIPPED = 'SHIPPED';  
    case DELIVERED = 'DELIVERED';
}
