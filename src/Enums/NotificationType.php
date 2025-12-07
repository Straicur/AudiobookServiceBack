<?php

declare(strict_types = 1);

namespace App\Enums;

enum NotificationType: int
{
    case NORMAL = 1;
    case ADMIN = 2;
    case PROPOSED = 3;
    case NEW_CATEGORY = 4;
    case NEW_AUDIOBOOK = 5;
    case USER_DELETE_DECLINE = 6;
    case USER_REPORT_ACCEPTED = 7;
    case USER_REPORT_DENIED = 8;
}
