<?php

namespace App\Enums;

/**
 * TechnicalBreakOrder
 */
enum TechnicalBreakOrder: int
{
    case LATEST = 1;
    case OLDEST = 2;
    case ACTIVE = 3;
}