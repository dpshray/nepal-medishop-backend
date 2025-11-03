<?php

namespace App\Enums\Purchase;

enum OrderTypeEnum: string
{
    case REGULAR = 'REGULAR';
    case KITBAG = 'KITBAG';
}
