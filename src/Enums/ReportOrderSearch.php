<?php

namespace App\Enums;

/**
 * ReportOrderSearch
 */
enum ReportOrderSearch: int
{
    case LATEST = 1;
    case OLDEST = 2;
}