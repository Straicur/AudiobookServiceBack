<?php

namespace App\Enums;

enum CacheValidTime: int
{
    case DAY = 86400;
    case HALF_A_DAY = 43200;
    case THREE_HOURS = 10800;
    case TWO_HOURS = 7200;
    case HOUR = 3600;
    case THIRTY_MINUTES = 1800;
    case TWENTY_MINUTES = 1200;
    case TEN_MINUTES = 600;
    case FIVE_MINUTES = 300;
}
