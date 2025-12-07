<?php

declare(strict_types = 1);

namespace App\Enums;

enum UserRolesNames: string
{
    case GUEST = 'Guest';
    case USER = 'User';
    case ADMINISTRATOR = 'Administrator';
    case RECRUITER = 'Recruiter';
}
