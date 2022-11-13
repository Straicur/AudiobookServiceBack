<?php

namespace App\Enums;

/**
 * UserRoles
 */
enum UserRoles: int
{
    case GUEST = 1;
    case USER = 2;
    case ADMINISTRATOR = 3;
}