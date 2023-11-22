<?php

namespace App\Model\Admin;

use App\Enums\AudiobookAgeRange;
use App\Model\Error\ModelInterface;
use OpenApi\Attributes as OA;

class AdminAudiobookDetailsSuccessModel implements ModelInterface
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
    private float $avgRating;
    private int $ratingAmount;
    private ?string $encoded;

    /**
     * @var AdminAudiobookCategoryModel[]
     */
    private array $categories = [];

    /**
     * @param string $id
     * @param string $title
     * @param string $author
     * @param string $version
     * @param string $album
     * @param \DateTime $year
     * @param int $duration
     * @param string $size
     * @param int $parts
     * @param string $description
     * @param AudiobookAgeRange $age
     * @param bool $active
     * @param float $avgRating
     * @param array $ratingAmount
     * @param float $avgRating
     */
    public function __construct(string $id, string $title, string $author, string $version, string $album, \DateTime $year, int $duration, string $size, int $parts, string $description, AudiobookAgeRange $age, bool $active, float $avgRating, array $categories, int $ratingAmount)
    {
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
        $this->avgRating = $avgRating;
        $this->ratingAmount = $ratingAmount;
        $this->categories = $categories;
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
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getAlbum(): string
    {
        return $this->album;
    }

    /**
     * @param string $album
     */
    public function setAlbum(string $album): void
    {
        $this->album = $album;
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
     * @return string|null
     */
    public function getEncoded(): ?string
    {
        return $this->encoded;
    }

    /**
     * @param string $encoded
     */
    public function setEncoded(string $encoded): void
    {
        $this->encoded = $encoded;
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
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
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
     * @return AdminAudiobookCategoryModel[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     */
    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    public function addCategory(AdminAudiobookCategoryModel $category)
    {
        $this->categories[] = $category;
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

    /**
     * @return int
     */
    public function getRatingAmount(): int
    {
        return $this->ratingAmount;
    }

    /**
     * @param int $ratingAmount
     */
    public function setRatingAmount(int $ratingAmount): void
    {
        $this->ratingAmount = $ratingAmount;
    }

}