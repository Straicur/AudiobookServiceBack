<?php

namespace App\Enums;

enum ProposedAudiobookCategoriesRanges: int
{
    case MOST_WANTED = 0;
    case WANTED = 1;
    case LESS_WANTED = 2;
    case PROPOSED = 3;
    case RANDOM = 4;
}