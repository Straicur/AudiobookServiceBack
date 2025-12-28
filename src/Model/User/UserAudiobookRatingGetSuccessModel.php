<?php

declare(strict_types = 1);

namespace App\Model\User;

use App\Model\ModelInterface;

class UserAudiobookRatingGetSuccessModel implements ModelInterface
{
    public function __construct(private int $ratingPercent) {}

    public function getRatingPercent(): int
    {
        return $this->ratingPercent;
    }

    public function setRatingPercent(int $ratingPercent): void
    {
        $this->ratingPercent = $ratingPercent;
    }
}
