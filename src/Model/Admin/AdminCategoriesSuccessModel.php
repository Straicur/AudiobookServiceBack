<?php

declare(strict_types = 1);

namespace App\Model\Admin;

use App\Model\ModelInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

class AdminCategoriesSuccessModel implements ModelInterface
{
    /**
     * @var AdminCategoryModel[]
     */
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: new Model(type: AdminCategoryModel::class))
    )]
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
