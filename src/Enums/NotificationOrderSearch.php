<?php

namespace App\Enums;

enum NotificationOrderSearch: int
{
    case LATEST = 1;
    case OLDEST = 2;
}