<?php

declare(strict_types = 1);

namespace App\Enums;

enum NotificationOrderSearch: int
{
    case LATEST = 1;
    case OLDEST = 2;
}
