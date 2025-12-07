<?php

declare(strict_types = 1);

namespace App\Model\User;

class UserAudiobookCategoryModel
{
    public function __construct(private string $name, private string $categoryKey) {}

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
