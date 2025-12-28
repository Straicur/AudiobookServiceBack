<?php

declare(strict_types = 1);

namespace App\Enums;

enum ReportOrderSearch: int
{
    case LATEST = 1;
    case OLDEST = 2;
}
