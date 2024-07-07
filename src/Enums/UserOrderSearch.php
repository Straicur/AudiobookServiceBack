<?php

namespace App\Enums;

enum UserOrderSearch: int
{
    case LATEST = 1;
    case OLDEST = 2;
    case ALPHABETICAL_ASC = 3;
    case ALPHABETICAL_DESC = 4;
}