<?php

namespace App\Enums;

enum GrievanceEnum:string
{
    case PENDING = "PENDING";
    case UNDER_PROCESS = "UNDER_PROCESS";
    case RESOLVED = "RESOLVED";
}
