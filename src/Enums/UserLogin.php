<?php

declare(strict_types = 1);

namespace App\Enums;

enum UserLogin: int
{
    case MAX_LOGIN_ATTEMPTS = 3;
}
