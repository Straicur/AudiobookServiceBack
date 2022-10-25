<?php

namespace App\Model;

use App\Enums\AudiobookAgeRange;
use OpenApi\Attributes as OA;

class AdminCategoryModel implements ModelInterface
{
    private string $name;
    private bool $active;
    private string $categoryKey;

    /**
     * @var AdminCategoryModel[]
     */
    private array $children = [];

    /**
     * @param string $name
     * @param bool $active
     * @param string $categoryKey
     */
    public function __construct(string $name, bool $active, string $categoryKey)
    {
        $this->name = $name;
        $this->active = $active;
        $this->categoryKey = $categoryKey;
    }

    /**
     * @return AdminCategoryModel[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param array $children
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    public function addChildren(AdminCategoryModel $children)
    {
        $this->children[] = $children;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

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