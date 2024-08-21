<?php

declare(strict_types=1);

namespace App\Model\User;

class UserCategoryTreeModel
{
    private string $name;
    private string $categoryKey;
    private ?string $parentCategoryKey;

    /**
     * @var UserCategoryTreeModel[]
     */
    private array $children = [];

    public function __construct(
        string $name,
        string $categoryKey,
        ?string $parentCategoryKey = null,
    ) {
        $this->name = $name;
        $this->categoryKey = $categoryKey;
        $this->parentCategoryKey = $parentCategoryKey;
    }

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
