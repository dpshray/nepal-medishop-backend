<?php

namespace App\Enums;

enum ProductUnitEnum: string
{
    case MG = 'mg';
    case G = 'g';
    case MCG = 'mcg';
    case ML = 'ml';
    case L = 'l';
    case IU = 'IU';
    case TABLET = 'tablet';
    case CAPSULE = 'capsule';
    case SACHET = 'sachet';
    case STRIP = 'strip';
    case PACK = 'pack';
    case AMPOULE = 'ampoule';
    case VIAL = 'vial';
    case TUBE = 'tube';
    case BOTTLE = 'bottle';
    case PUFF = 'puff';
    case PATCH = 'patch';
}
