<?php

namespace App\Enums;

enum UserBanType: int
{
    case SPAM = 1;
    case COMMENT = 2;
    case STRANGE_BEHAVIOR = 3;
    case MAX_LOGINS_BREAK = 4;
}
