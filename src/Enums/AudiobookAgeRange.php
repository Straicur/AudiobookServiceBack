<?php

namespace App\Enums;

/**
 * AudiobookAgeRange
 */
enum AudiobookAgeRange: int
{
    case FROM3TO7 = 1;
    case FROM7TO12 = 2;
    case FROM12TO16 = 3;
    case FROM16TO18 = 4;
    case ABOVE18 = 5;
}