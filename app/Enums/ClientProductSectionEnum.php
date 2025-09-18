<?php

namespace App\Enums;

enum ClientProductSectionEnum: string
{
    case RECENT = 'recent';
    case FEATURED = 'featured';
    case FLASH = 'flash';
    case POPULAR = 'popular';
    case BEST_SELLER = 'best_seller';
}
