<?php

declare(strict_types=1);

namespace App\Model\User;

use App\Model\ModelInterface;

class UserAudiobookRatingGetSuccessModel implements ModelInterface
{
    private int $ratingPercent;

    public function __construct(int $ratingPercent)
    {
        $this->ratingPercent = $ratingPercent;
    }


    public function getRatingPercent(): int
    {
        return $this->ratingPercent;
    }

    public function setRatingPercent(int $ratingPercent): void
    {
        $this->ratingPercent = $ratingPercent;
    }
}
