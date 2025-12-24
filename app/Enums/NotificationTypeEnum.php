<?php

namespace App\Enums;

enum NotificationTypeEnum: string 
{
    case ORDER = 'ORDER';
    case VendorProductApproval = 'VENDOR_PRODUCT_APPROVAL';
    case PUSH_NOTIFICATION = 'PUSH_NOTIFICATION';
}
