<?php

declare(strict_types=1);

namespace App\Model\User;

class UserAudiobookCategoryModel
{
    private string $name;
    private string $categoryKey;

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