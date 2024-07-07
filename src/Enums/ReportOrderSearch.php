<?php

namespace App\Enums;

enum ReportOrderSearch: int
{
    case LATEST = 1;
    case OLDEST = 2;
}