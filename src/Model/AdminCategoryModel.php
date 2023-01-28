<?php

namespace App\Model;

class AdminCategoryModel implements ModelInterface
{
    private string $id;
    private string $name;
    private bool $active;
    private string $categoryKey;
    private ?string $parentCategoryKey;

    /**
     * @var AdminCategoryModel[]
     */
    private array $children = [];

    /**
     * @param string $id
     * @param string $name
     * @param bool $active
     * @param string $categoryKey
     * @param string|null $parentCategoryKey
     */
    public function __construct(string $id, string $name, bool $active, string $categoryKey, ?string $parentCategoryKey = null)
    {
        $this->id = $id;
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

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getParentCategoryKey(): ?string
    {
        return $this->parentCategoryKey;
    }

    /**
     * @param string|null $parentCategoryKey
     */
    public function setParentCategoryKey(?string $parentCategoryKey): void
    {
        $this->parentCategoryKey = $parentCategoryKey;
    }

}