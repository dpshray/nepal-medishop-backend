<?php

namespace App\Enums\Purchase;

enum OrderItemStatusEnum:string
{

    case PENDING = 'PENDING';
    case ASSIGNED = 'ASSIGNED';
    case DELIVERED = 'DELIVERED';
}
