<?php

namespace App\Model;

/**
 * TenzieAllModel
 *
 */
class TenzieAllModel
{
    private int $level;
    private string $title;
    private string $time;
    private int $dateAdd;

    /**
     * @param int $level
     * @param string $title
     * @param string $time
     * @param \DateTime $dateAdd
     */
    public function __construct(int $level, string $title, string $time, \DateTime $dateAdd)
    {
        $this->level = $level;
        $this->title = $title;
        $this->time = $time;
        $this->dateAdd = $dateAdd->getTimestamp();
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     */
    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTime(): string
    {
        return $this->time;
    }

    /**
     * @param string $time
     */
    public function setTime(string $time): void
    {
        $this->time = $time;
    }

    /**
     * @return int
     */
    public function getDateAdd(): int
    {
        return $this->dateAdd;
    }

    /**
     * @param \DateTime $dateAdd
     */
    public function setDateAdd(\DateTime $dateAdd): void
    {
        $this->dateAdd = $dateAdd->getTimestamp();
    }

}