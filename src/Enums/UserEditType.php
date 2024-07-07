<?php

namespace App\Enums;

enum UserEditType: int
{
    case EMAIL = 1;
    case PASSWORD = 2;
}