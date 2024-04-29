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
    private array $categories = [];

    private bool $inList;
    private int $comments;
    private bool $canRate = false;
    private bool $canComment = false;
    private ?string $imgFile;

    /**
     * @param string $id
     * @param string $title
     * @param string $author
     * @param string $version
     * @param string $album
     * @param DateTime $year
     * @param string $duration
     * @param int $parts
     * @param string $description
     * @param AudiobookAgeRange $age
     * @param AudiobookDetailCategoryModel[] $categories
     * @param bool $inList
     * @param int $comments
     * @param float $avgRating
     * @param int $ratingAmount
     * @param string|null $imgFile
     */
    public function __construct(
        string            $id,
        string            $title,
        string            $author,
        string            $version,
        string            $album,
        DateTime          $year,
        string            $duration,
        int               $parts,
        string            $description,
        AudiobookAgeRange $age,
        array             $categories,
        bool              $inList,
        int               $comments,
        float             $avgRating,
        int               $ratingAmount,
        ?string           $imgFile
    )
    {
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
     * @param DateTime $year
     */
    public function setYear(DateTime $year): void
    {
        $this->year = $year->getTimestamp() * 1000;
    }

    /**
     * @return string
     */
    public function getDuration(): string
    {
        return $this->duration;
    }

    /**
     * @param string $duration
     */
    public function setDuration(string $duration): void
    {
        $this->duration = $duration;
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

    /**
     * @param array $categories
     */
    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    public function addCategory(AudiobookDetailCategoryModel $category): void
    {
        $this->categories[] = $category;
    }

    /**
     * @return bool
     */
    public function isInList(): bool
    {
        return $this->inList;
    }

    /**
     * @param bool $inList
     */
    public function setInList(bool $inList): void
    {
        $this->inList = $inList;
    }

    /**
     * @return int
     */
    public function getComments(): int
    {
        return $this->comments;
    }

    /**
     * @param int $comments
     */
    public function setComments(int $comments): void
    {
        $this->comments = $comments;
    }

    /**
     * @return bool
     */
    public function isCanRate(): bool
    {
        return $this->canRate;
    }

    /**
     * @param bool $canRate
     */
    public function setCanRate(bool $canRate): void
    {
        $this->canRate = $canRate;
    }

    /**
     * @return bool
     */
    public function isCanComment(): bool
    {
        return $this->canComment;
    }

    /**
     * @param bool $canComment
     */
    public function setCanComment(bool $canComment): void
    {
        $this->canComment = $canComment;
    }

    /**
     * @param bool $avgRating
     */
    public function setAvgRating(bool $avgRating): void
    {
        $this->avgRating = $avgRating;
    }

    /**
     * @return float
     */
    public function getAvgRating(): float
    {
        return $this->avgRating;
    }

    /**
     * @param int $ratingAmount
     */
    public function setRatingAmount(int $ratingAmount): void
    {
        $this->ratingAmount = $ratingAmount;
    }

    /**
     * @return int
     */
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

}