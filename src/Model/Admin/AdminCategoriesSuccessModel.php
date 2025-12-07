<?php

declare(strict_types = 1);

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
        if (null !== $categories) {
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

    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    public function addCategory(AdminCategoryModel $category): void
    {
        $this->categories[] = $category;
    }
}
