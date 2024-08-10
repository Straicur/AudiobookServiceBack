<?php

namespace App\Enums;

enum ReportLimits: int
{
    case IP_LIMIT = 3;
    case EMAIL_LIMIT = 5;
}
