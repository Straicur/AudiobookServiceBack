<?php

namespace App\Enums;

enum UserAudiobookActivationType: int
{
    case ALL = 1;
    case CATEGORY_PROPOSED_RELATED = 2;
    case MY_LIST_RELATED = 3;
    case AUDIOBOOK_INFO_RELATED = 4;
}
