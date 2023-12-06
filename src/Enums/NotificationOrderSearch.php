<?php

namespace App\Enums;

/**
 * NotificationOrderSearch
 */
enum NotificationOrderSearch: int
{
    case LATEST = 1;
    case OLDEST = 2;
}