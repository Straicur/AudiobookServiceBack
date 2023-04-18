<?php

namespace App\Enums;

/**
 * UserRolesNames
 */
enum UserRolesNames: string
{
    case GUEST = "Guest";
    case USER = "User";
    case ADMINISTRATOR = "Administrator";
}