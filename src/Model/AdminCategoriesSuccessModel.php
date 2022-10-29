<?php

namespace App\Model;

class AdminCategoriesSuccessModel implements ModelInterface
{
    /**
     * @var AdminCategoryModel[]
     */
    private array $categories = [];

    /**
     * @param AdminCategoryModel[] $categories
     */
    public function __construct(array $categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return AdminCategoryModel[]
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

    public function addCategory(AdminCategoryModel $category)
    {
        $this->categories[] = $category;
    }
}