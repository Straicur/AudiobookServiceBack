<?php

namespace App\Enums;


/**
 * ProposedAudiobooksRanges
 */
enum ProposedAudiobooksRanges: int
{
    case MOST_WANTED_LIMIT = 5;
    case WANTED_LIMIT = 4;
    case LESS_WANTED_LIMIT = 3;
    case PROPOSED_LIMIT = 2;
    case RANDOM_LIMIT = 1;
}