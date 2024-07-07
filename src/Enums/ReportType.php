<?php

namespace App\Enums;

enum ReportType: int
{
    case COMMENT = 1;
    case AUDIOBOOK_PROBLEM = 2;
    case CATEGORY_PROBLEM = 3;
    case SYSTEM_PROBLEM = 4;
    case USER_PROBLEM = 5;
    case SETTINGS_PROBLEM = 6;
}
