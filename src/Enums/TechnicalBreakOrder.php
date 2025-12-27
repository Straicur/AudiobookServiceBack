<?php

declare(strict_types = 1);

namespace App\Enums;

enum TechnicalBreakOrder: int
{
    case LATEST = 1;
    case OLDEST = 2;
    case ACTIVE = 3;
}
