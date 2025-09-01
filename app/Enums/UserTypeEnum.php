<?php

namespace App\Enums;

enum UserTypeEnum: int
{
    case ADMIN = 1;
    case VENDOR = 2;
    case USER = 3;

    public function isEqualTo(self $type): bool
    {
        return $this === $type;
    }
}
