<?php

namespace App\Enums;

/**
 * BanPeriodRage
 */
enum BanPeriodRage: string
{
    case NOT_BANNED = "";
    case HALF_DAY_BAN = "12 hour";
    case ONE_DAY_BAN = "24 hour";
    case FIVE_DAY_BAN = "5 day";
    case ONE_MONTH_BAN = "1 month";
    case THREE_MONTH_BAN = "3 month";
    case ONE_YEAR_BAN = "1 year";
}