<?php

namespace App\Enums;

enum ProductStatusEnum: string
{
    case ACTIVE = 'ACTIVE';
    case INACTIVE = 'INACTIVE';
    case OUT_OF_STOCK = 'OUT_OF_STOCK'; 
}
