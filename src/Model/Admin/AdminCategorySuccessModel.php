<?php

declare(strict_types=1);

namespace App\Model\Admin;

use App\Model\ModelInterface;

class AdminCategorySuccessModel implements ModelInterface
{
    private string $id;
    private string $name;
    private bool $active;
    private ?string $parentCategoryName;
    private ?string $parentCategoryId;

    public function __construct(string $id, string $name, bool $active, ?string $parentCategoryName = null, ?string $parentCategoryId = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->active = $active;
        $this->parentCategoryName = $parentCategoryName;
        $this->parentCategoryId = $parentCategoryId;
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

    public function getParentCategoryName(): ?string
    {
        return $this->parentCategoryName;
    }

    public function setParentCategoryName(?string $parentCategoryName): void
    {
        $this->parentCategoryName = $parentCategoryName;
    }

    public function getParentCategoryId(): ?string
    {
        return $this->parentCategoryId;
    }

    public function setParentCategoryId(?string $parentCategoryId): void
    {
        $this->parentCategoryId = $parentCategoryId;
    }

}