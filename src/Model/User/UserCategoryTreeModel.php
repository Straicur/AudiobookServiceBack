<?php

declare(strict_types = 1);

namespace App\Model\User;

use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

class UserCategoryTreeModel
{
    /**
     * @var UserCategoryTreeModel[]
     */
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: new Model(type: UserCategoryTreeModel::class))
    )]
    private array $children = [];

    public function __construct(private string $name, private string $categoryKey, private ?string $parentCategoryKey = null) {}

    /**
     * @return UserCategoryTreeModel[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    public function addChildren(UserCategoryTreeModel $children): void
    {
        $this->children[] = $children;
    }

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

    public function getParentCategoryKey(): ?string
    {
        return $this->parentCategoryKey;
    }

    public function setParentCategoryKey(?string $parentCategoryKey): void
    {
        $this->parentCategoryKey = $parentCategoryKey;
    }
}
