<?php

declare(strict_types = 1);

namespace App\Enums;

enum UserEditType: int
{
    case EMAIL = 1;
    case PASSWORD = 2;
    case PASSWORD_RESET = 3;
    case USER_DATA = 4;
    case EMAIL_CODE = 5;
}
