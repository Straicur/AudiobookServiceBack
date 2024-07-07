<?php

namespace App\Enums;

enum UserRolesNames: string
{
    case GUEST = 'Guest';
    case USER = 'User';
    case ADMINISTRATOR = 'Administrator';
}
