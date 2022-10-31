<?php

namespace App\Enums;


/**
 * ProposedAudiobooksRanges
 */
enum ProposedAudiobooksRanges: int
{
    case MOSTWANTED = 5;
    case WANTED = 4;
    case LESSWANTED = 3;
    case PROPOSED = 2;
    case RANDOM = 1;
    case NONE = 0;
}