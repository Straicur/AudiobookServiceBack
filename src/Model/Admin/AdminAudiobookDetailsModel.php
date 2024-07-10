<?php

declare(strict_types=1);

namespace App\Model\Admin;

use App\Enums\AudiobookAgeRange;
use App\Model\Common\AudiobookDetailCategoryModel;
use DateTime;
use OpenApi\Attributes as OA;

class AdminAudiobookDetailsModel
{
    private string $id;
    private string $title;
    private string $author;
    private string $version;
    private string $album;
    private int $year;
    private int $duration;
    private string $size;
    private int $parts;
    private string $description;
    private int $age;
    private bool $active;
    private ?string $encoded;

    /**
     * @var AudiobookDetailCategoryModel[]
     */
    private array $categories;

    public function __construct(
        string $id,
        string $title,
        string $author,
        string $version,
        string $album,
        DateTime $year,
        int $duration,
        string $size,
        int $parts,
        string $description,
        AudiobookAgeRange $age,
        bool $active,
        array $categories
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->version = $version;
        $this->album = $album;
        $this->year = $year->getTimestamp() * 1000;
        $this->duration = $duration;
        $this->size = $size;
        $this->parts = $parts;
        $this->description = $description;
        $this->age = $age->value;
        $this->active = $active;
        $this->categories = $categories;
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

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function getAlbum(): string
    {
        return $this->album;
    }

    public function setAlbum(string $album): void
    {
        $this->album = $album;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(DateTime $year): void
    {
        $this->year = $year->getTimestamp() * 1000;
    }

    public function getEncoded(): ?string
    {
        return $this->encoded;
    }

    public function setEncoded(string $encoded): void
    {
        $this->encoded = $encoded;
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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
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

    /**
     * @return AudiobookDetailCategoryModel[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    public function addCategory(AudiobookDetailCategoryModel $category): void
    {
        $this->categories[] = $category;
    }
}
