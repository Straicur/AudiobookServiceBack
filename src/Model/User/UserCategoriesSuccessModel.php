<?php

declare(strict_types = 1);

namespace App\Model\User;

use App\Model\ModelInterface;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

class UserCategoriesSuccessModel implements ModelInterface
{
    /**
     * @var UserCategoryTreeModel[]
     */
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: new Model(type: UserCategoryTreeModel::class))
    )]
    private array $categories = [];

    /**
     * @param UserCategoryTreeModel[] $categories
     */
    public function __construct(?array $categories = null)
    {
        if (null !== $categories) {
            $this->categories = $categories;
        }
    }

    /**
     * @return UserCategoryTreeModel[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    public function addCategory(UserCategoryTreeModel $category): void
    {
        $this->categories[] = $category;
    }
}
