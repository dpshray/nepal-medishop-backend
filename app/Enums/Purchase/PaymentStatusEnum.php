<?php

namespace App\Enums\Purchase;

enum PaymentStatusEnum: string
{
    case PENDING = 'PENDING'; 
    case PAID = 'PAID';  
    case FAILED = 'FAILED'; 
}
