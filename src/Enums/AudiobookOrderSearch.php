<?php

namespace App\Enums;

enum AudiobookOrderSearch: int
{
    case POPULAR = 1;
    case LEST_POPULAR = 2;
    case LATEST = 3;
    case OLDEST = 4;
    case ALPHABETICAL_ASC = 5;
    case ALPHABETICAL_DESC = 6;
    case TOP_RATED = 7;
    case WORST_RATED = 8;
}
