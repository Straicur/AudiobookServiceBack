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

    public function __construct(string $id, string $title, string $author, int $parts, AudiobookAgeRange $age, ?string $imgFile)
    {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->parts = $parts;
        $this->age = $age->value;
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

    /**
     * @return UserAudiobookCategoryModel[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

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