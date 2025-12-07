<?php

declare(strict_types = 1);

namespace App\Enums;

enum NotificationUserType: int
{
    case ADMIN = 1;
    case SYSTEM = 2;
}
