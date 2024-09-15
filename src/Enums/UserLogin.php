<?php

namespace App\Enums;

enum UserLogin: int
{
    case MAX_LOGIN_ATTEMPTS = 3;
}
