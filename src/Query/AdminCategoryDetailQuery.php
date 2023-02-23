<?php

namespace App\Query;

use Symfony\Component\Validator\Constraints as Assert;

class AdminCategoryDetailQuery
{
    #[Assert\NotNull(message: "CategoryKey is null")]
    #[Assert\NotBlank(message: "CategoryKey is empty")]
    #[Assert\Type(type: "string")]
    private string $categoryKey;

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