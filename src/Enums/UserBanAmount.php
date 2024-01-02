<?php

namespace App\Enums;

/**
 * UserBanAmount
 */
enum UserBanAmount: int
{
    case NONE = 0;
    case LOW = 2;
    case MEDIUM = 5;
    case HIGH = 7;
    case HIGHEST = 15;
}