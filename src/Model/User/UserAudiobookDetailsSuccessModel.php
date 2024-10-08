<?php

declare(strict_types=1);

namespace App\Model\User;

use App\Enums\AudiobookAgeRange;
use App\Model\Common\AudiobookDetailCategoryModel;
use App\Model\ModelInterface;
use DateTime;
use OpenApi\Attributes as OA;

class UserAudiobookDetailsSuccessModel implements ModelInterface
{
    private string $id;
    private string $title;
    private string $author;
    private string $version;
    private string $album;
    private int $year;
    private string $duration;
    private int $parts;
    private string $description;
    private int $age;
    private float $avgRating;
    private int $ratingAmount;

    /**
     * @var AudiobookDetailCategoryModel[]
     */
    private array $categories;

    private bool $inList;
    private int $comments;
    private bool $canRate = false;
    private bool $canComment = false;
    private bool $rated = false;
    private ?string $imgFile;

    public function __construct(
        string $id,
        string $title,
        string $author,
        string $version,
        string $album,
        DateTime $year,
        string $duration,
        int $parts,
        string $description,
        AudiobookAgeRange $age,
        array $categories,
        bool $inList,
        int $comments,
        float $avgRating,
        int $ratingAmount,
        ?string $imgFile
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->version = $version;
        $this->album = $album;
        $this->year = $year->getTimestamp() * 1000;
        $this->duration = $duration;
        $this->parts = $parts;
        $this->description = $description;
        $this->age = $age->value;
        $this->categories = $categories;
        $this->inList = $inList;
        $this->comments = $comments;
        $this->avgRating = $avgRating;
        $this->ratingAmount = $ratingAmount;
        $this->imgFile = $imgFile;
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

    public function getDuration(): string
    {
        return $this->duration;
    }

    public function setDuration(string $duration): void
    {
        $this->duration = $duration;
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

    public function isInList(): bool
    {
        return $this->inList;
    }

    public function setInList(bool $inList): void
    {
        $this->inList = $inList;
    }

    public function getComments(): int
    {
        return $this->comments;
    }

    public function setComments(int $comments): void
    {
        $this->comments = $comments;
    }

    public function isCanRate(): bool
    {
        return $this->canRate;
    }

    public function setCanRate(bool $canRate): void
    {
        $this->canRate = $canRate;
    }

    public function isCanComment(): bool
    {
        return $this->canComment;
    }

    public function setCanComment(bool $canComment): void
    {
        $this->canComment = $canComment;
    }

    public function setAvgRating(float $avgRating): void
    {
        $this->avgRating = $avgRating;
    }

    public function getAvgRating(): float
    {
        return $this->avgRating;
    }

    public function setRatingAmount(int $ratingAmount): void
    {
        $this->ratingAmount = $ratingAmount;
    }

    public function getRatingAmount(): int
    {
        return $this->ratingAmount;
    }

    public function getImgFile(): ?string
    {
        return $this->imgFile;
    }

    public function setImgFile(?string $imgFile): void
    {
        $this->imgFile = $imgFile;
    }

    public function isRated(): bool
    {
        return $this->rated;
    }

    public function setRated(bool $rated): void
    {
        $this->rated = $rated;
    }
}
