<?php

namespace App\Model;

class UserAudiobookInfoSuccessModel implements ModelInterface
{
    private int $part;
    private string $endedTime;
    private int $watchingDate;

    /**
     * @param int $part
     * @param string $endedTime
     * @param \DateTime $watchingDate
     */
    public function __construct(int $part, string $endedTime, \DateTime $watchingDate)
    {
        $this->part = $part;
        $this->endedTime = $endedTime;
        $this->watchingDate = $watchingDate->getTimestamp();
    }

    /**
     * @return int
     */
    public function getPart(): int
    {
        return $this->part;
    }

    /**
     * @param int $part
     */
    public function setPart(int $part): void
    {
        $this->part = $part;
    }

    /**
     * @return string
     */
    public function getEndedTime(): string
    {
        return $this->endedTime;
    }

    /**
     * @param string $endedTime
     */
    public function setEndedTime(string $endedTime): void
    {
        $this->endedTime = $endedTime;
    }

    /**
     * @return int
     */
    public function getWatchingDate(): int
    {
        return $this->watchingDate;
    }

    /**
     * @param \DateTime $watchingDate
     */
    public function setWatchingDate(\DateTime $watchingDate): void
    {
        $this->watchingDate = $watchingDate->getTimestamp();
    }


}