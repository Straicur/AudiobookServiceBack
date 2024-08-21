<?php

namespace App\Query\User;

use Symfony\Component\Validator\Constraints as Assert;

class UserAudiobooksSearchQuery
{
    #[Assert\NotNull(message: 'Title is null')]
    #[Assert\Type(type: 'string')]
    private string $title;

    #[Assert\NotNull(message: 'Category key is null')]
    #[Assert\Type(type: 'string')]
    private string $categoryKey;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
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
