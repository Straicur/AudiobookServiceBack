<?php

declare(strict_types=1);

namespace App\Model\User;

class UserCategoryModel
{
    private string $name;
    private string $categoryKey;

    /**
     * @var UserAudiobookModel[]
     */
    private array $audiobooks = [];

    public function __construct(string $name, string $categoryKey)
    {
        $this->name = $name;
        $this->categoryKey = $categoryKey;
    }

    /**
     * @return UserAudiobookModel[]
     */
    public function getAudiobooks(): array
    {
        return $this->audiobooks;
    }

    /**
     * @param array $audiobooks
     */
    public function setAudiobooks(array $audiobooks): void
    {
        $this->audiobooks = $audiobooks;
    }

    public function addAudiobook(UserAudiobookModel $audiobook): void
    {
        $this->audiobooks[] = $audiobook;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCategoryKey(): string
    {
        return $this->categoryKey;
    }

    public function setCategoryKey(string $categoryKey): void
    {
        $this->categoryKey = $categoryKey;
    }

}