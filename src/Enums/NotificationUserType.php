<?php

namespace App\Enums;

/**
 * NotificationUserType
 */
enum NotificationUserType: int
{
    case ADMIN = 1;
    case SYSTEM = 2;
}