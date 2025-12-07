<?php

declare(strict_types = 1);

namespace App\Model\Admin;

class AdminCategoryModel
{
    /**
     * @var AdminCategoryModel[]
     */
    private array $children = [];

    public function __construct(private string $id, private string $name, private bool $active, private string $categoryKey, private ?int $audiobooks = null, private ?string $parentCategoryKey = null) {}

    /**
     * @return AdminCategoryModel[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    public function addChildren(AdminCategoryModel $children): void
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

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getCategoryKey(): string
    {
        return $this->categoryKey;
    }

    public function setCategoryKey(string $categoryKey): void
    {
        $this->categoryKey = $categoryKey;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getParentCategoryKey(): ?string
    {
        return $this->parentCategoryKey;
    }

    public function setParentCategoryKey(?string $parentCategoryKey): void
    {
        $this->parentCategoryKey = $parentCategoryKey;
    }

    public function getAudiobooks(): ?int
    {
        return $this->audiobooks;
    }

    public function setAudiobooks(int $audiobooks): void
    {
        $this->audiobooks = $audiobooks;
    }
}
