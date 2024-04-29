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

    /**
     * @param string $name
     * @param string $categoryKey
     */
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

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getCategoryKey(): string
    {
        return $this->categoryKey;
    }

    /**
     * @param string $categoryKey
     */
    public function setCategoryKey(string $categoryKey): void
    {
        $this->categoryKey = $categoryKey;
    }

}