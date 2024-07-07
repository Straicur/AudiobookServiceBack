<?php

declare(strict_types=1);

namespace App\Model\Common;

use App\Model\ModelInterface;

class AudiobookDetailCategoryModel
{
    private string $id;
    private string $name;
    private bool $active;
    private string $categoryKey;

    public function __construct(string $id, string $name, bool $active, string $categoryKey)
    {
        $this->id = $id;
        $this->name = $name;
        $this->active = $active;
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

}