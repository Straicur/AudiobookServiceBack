<?php

declare(strict_types = 1);

namespace App\Enums;

enum UserRoles: int
{
    case GUEST = 1;
    case USER = 2;
    case ADMINISTRATOR = 3;
    case RECRUITER = 4;
}
