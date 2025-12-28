<?php

declare(strict_types = 1);

namespace App\Model\Admin;

use App\Enums\AudiobookAgeRange;
use DateTime;
use OpenApi\Attributes as OA;

class AdminCategoryAudiobookModel
{
    private int $year;

    private int $age;

    public function __construct(
        private string $id,
        private string $title,
        private string $author,
        DateTime $year,
        private int $duration,
        private string $size,
        private int $parts,
        private float $avgRating,
        AudiobookAgeRange $age,
        private bool $active,
    ) {
        $this->year = $year->getTimestamp() * 1000;
        $this->age = $age->value;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(DateTime $year): void
    {
        $this->year = $year->getTimestamp() * 1000;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): void
    {
        $this->duration = $duration;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    public function setSize(string $size): void
    {
        $this->size = $size;
    }

    public function getParts(): int
    {
        return $this->parts;
    }

    public function setParts(int $parts): void
    {
        $this->parts = $parts;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    #[OA\Property(type: 'integer', enum: [1 => 'FROM3TO7', 2 => 'FROM7TO12', 3 => 'FROM12TO16', 4 => 'FROM16TO18', 5 => 'ABOVE18'])]
    public function setAge(AudiobookAgeRange $age): void
    {
        $this->age = $age->value;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getAvgRating(): float
    {
        return $this->avgRating;
    }

    public function setAvgRating(float $avgRating): void
    {
        $this->avgRating = $avgRating;
    }
}
