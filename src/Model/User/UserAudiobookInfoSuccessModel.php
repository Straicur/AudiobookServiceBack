<?php

declare(strict_types=1);

namespace App\Model\User;

use App\Model\ModelInterface;
use DateTime;

class UserAudiobookInfoSuccessModel implements ModelInterface
{
    private int $part;
    private int $endedTime;
    private int $watchingDate;

    public function __construct(int $part, int $endedTime, DateTime $watchingDate)
    {
        $this->part = $part;
        $this->endedTime = $endedTime;
        $this->watchingDate = $watchingDate->getTimestamp() * 1000;
    }

    public function getPart(): int
    {
        return $this->part;
    }

    public function setPart(int $part): void
    {
        $this->part = $part;
    }

    public function getEndedTime(): int
    {
        return $this->endedTime;
    }

    public function setEndedTime(int $endedTime): void
    {
        $this->endedTime = $endedTime;
    }
    
    public function getWatchingDate(): int
    {
        return $this->watchingDate;
    }

    public function setWatchingDate(DateTime $watchingDate): void
    {
        $this->watchingDate = $watchingDate->getTimestamp() * 1000;
    }


}