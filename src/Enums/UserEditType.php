<?php

namespace App\Enums;

/**
 * UserEditType
 */
enum UserEditType: int
{
    case EMAIL = 1;
    case PASSWORD = 2;
}