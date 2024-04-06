<?php

declare(strict_types=1);

namespace App\Model\User;

use App\Enums\AudiobookAgeRange;
use OpenApi\Attributes as OA;

class UserAudiobookDetailModel
{
    private string $id;
    private string $title;
    private string $author;
    private int $parts;
    private int $age;
    /**
     * @var UserAudiobookCategoryModel[]
     */
    private array $categories = [];
    private ?string $imgFile;

    /**
     * @param string $id
     * @param string $title
     * @param string $author
     * @param int $parts
     * @param AudiobookAgeRange $age
     * @param string|null $imgFile
     */
    public function __construct(string $id, string $title, string $author, int $parts, AudiobookAgeRange $age, ?string $imgFile)
    {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->parts = $parts;
        $this->age = $age->value;
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
    #[OA\Property(type: 'integer', enum: [1 => 'FROM3TO7', 2 => 'FROM7TO12', 3 => 'FROM12TO16', 4 => 'FROM16TO18', 5 => 'ABOVE18'])]
    public function setAge(AudiobookAgeRange $age): void
    {
        $this->age = $age->value;
    }

    /**
     * @return UserAudiobookCategoryModel[]
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

    public function addCategory(UserAudiobookCategoryModel $category): void
    {
        $this->categories[] = $category;
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