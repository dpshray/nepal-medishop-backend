<?php

namespace App\Enums\Purchase;

enum ServiceBookingStatusEnum:string
{
    case PENDING = 'PENDING';
    case ASSIGNED = 'ASSIGNED';
}
