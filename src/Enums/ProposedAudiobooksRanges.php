<?php

declare(strict_types = 1);

namespace App\Enums;

enum ProposedAudiobooksRanges: int
{
    case RANDOM_LIMIT = 1;
    case PROPOSED_LIMIT = 2;
    case LESS_WANTED_LIMIT = 3;
    case WANTED_LIMIT = 4;
    case MOST_WANTED_LIMIT = 5;
}
