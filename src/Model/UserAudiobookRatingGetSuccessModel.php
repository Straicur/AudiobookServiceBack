<?php

namespace App\Model;

class UserAudiobookRatingGetSuccessModel implements ModelInterface
{
    private int $ratingPercent;

    /**
     * @param int $ratingPercent
     */
    public function __construct(int $ratingPercent)
    {
        $this->ratingPercent = $ratingPercent;
    }

    /**
     * @return int
     */
    public function getRatingPercent(): int
    {
        return $this->ratingPercent;
    }

    /**
     * @param int $ratingPercent
     */
    public function setRatingPercent(int $ratingPercent): void
    {
        $this->ratingPercent = $ratingPercent;
    }

}