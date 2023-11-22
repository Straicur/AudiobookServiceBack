<?php

namespace App\Model\Admin;

use App\Model\ModelInterface;

class AdminCategoriesSuccessModel implements ModelInterface
{
    /**
     * @var AdminCategoryModel[]
     */
    private array $categories = [];

    /**
     * @param AdminCategoryModel[] $categories
     */
    public function __construct(?array $categories = null)
    {
        if ($categories != null) {
            $this->categories = $categories;
        }
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