<?php

namespace App\Model\Admin;

use App\Enums\AudiobookAgeRange;
use OpenApi\Attributes as OA;

class AdminCategoryAudiobookModel
{
    private string $id;
    private string $title;
    private string $author;
    private int $year;
    private int $duration;
    private string $size;
    private int $parts;
    private int $age;
    private bool $active;
    private float $avgRating;

    /**
     * @param string $id
     * @param string $title
     * @param string $author
     * @param \DateTime $year
     * @param int $duration
     * @param string $size
     * @param int $parts
     * @param float $avgRating
     * @param AudiobookAgeRange $age
     * @param bool $active
     */
    public function __construct(string $id, string $title, string $author, \DateTime $year, int $duration, string $size, int $parts, float $avgRating, AudiobookAgeRange $age, bool $active)
    {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->year = $year->getTimestamp() * 1000;
        $this->duration = $duration;
        $this->size = $size;
        $this->parts = $parts;
        $this->avgRating = $avgRating;
        $this->age = $age->value;
        $this->active = $active;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
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
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    /**
     * @return int
     */
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * @param \DateTime $year
     */
    public function setYear(\DateTime $year): void
    {
        $this->year = $year->getTimestamp() * 1000;
    }

    /**
     * @return int
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    /**
     * @param int $duration
     */
    public function setDuration(int $duration): void
    {
        $this->duration = $duration;
    }

    /**
     * @return string
     */
    public function getSize(): string
    {
        return $this->size;
    }

    /**
     * @param string $size
     */
    public function setSize(string $size): void
    {
        $this->size = $size;
    }

    /**
     * @return int
     */
    public function getParts(): int
    {
        return $this->parts;
    }

    /**
     * @param int $parts
     */
    public function setParts(int $parts): void
    {
        $this->parts = $parts;
    }

    /**
     * @return int
     */
    public function getAge(): int
    {
        return $this->age;
    }

    /**
     * @param AudiobookAgeRange $age
     */
    #[OA\Property(type: "integer", enum: [1 => 'FROM3TO7', 2 => 'FROM7TO12', 3 => 'FROM12TO16', 4 => 'FROM16TO18', 5 => 'ABOVE18'])]
    public function setAge(AudiobookAgeRange $age): void
    {
        $this->age = $age->value;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return float
     */
    public function getAvgRating(): float
    {
        return $this->avgRating;
    }

    /**
     * @param float $avgRating
     */
    public function setAvgRating(float $avgRating): void
    {
        $this->avgRating = $avgRating;
    }

}