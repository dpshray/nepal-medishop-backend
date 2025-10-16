<?php

namespace App\Enums\Purchase;

enum PaymentStatusEnum: string
{
    case INITIATED = 'INITIATED'; 
    case PENDING = 'PENDING'; 
    case PAID = 'PAID';  
    case UNPAID = 'UNPAID'; 
    case FAILED = 'FAILED'; 
}
