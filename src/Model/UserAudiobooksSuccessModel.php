<?php

namespace App\Model;

class UserAudiobooksSuccessModel implements ModelInterface
{
    /**
     * @var UserCategoryModel[]
     */
    private array $categories = [];

    /**
     * @return UserCategoryModel[]
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

    public function addCategory(UserCategoryModel $category)
    {
        $this->categories[] = $category;
    }
}