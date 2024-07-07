<?php

namespace App\Enums;

enum BanPeriodRage: string
{
    case SYSTEM = '';
    case NOT_BANNED = '+0 day';
    case HOUR_DAY_BAN = '+1 hour';
    case HALF_DAY_BAN = '+12 hour';
    case ONE_DAY_BAN = '+24 hour';
    case FIVE_DAY_BAN = '+5 day';
    case ONE_MONTH_BAN = '+1 month';
    case THREE_MONTH_BAN = '+3 month';
    case ONE_YEAR_BAN = '+1 year';
}