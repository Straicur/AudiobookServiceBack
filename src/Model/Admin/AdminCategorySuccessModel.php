<?php

namespace App\Model\Admin;

use App\Model\ModelInterface;

class AdminCategorySuccessModel implements ModelInterface
{
    private string $id;
    private string $name;
    private bool $active;
    private ?string $parentCategoryName;
    private ?string $parentCategoryId;

    /**
     * @param string $id
     * @param string $name
     * @param bool $active
     * @param string|null $parentCategoryName
     * @param string|null $parentCategoryId
     */
    public function __construct(string $id, string $name, bool $active, ?string $parentCategoryName = null, ?string $parentCategoryId = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->active = $active;
        $this->parentCategoryName = $parentCategoryName;
        $this->parentCategoryId = $parentCategoryId;
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
     * @return string|null
     */
    public function getParentCategoryName(): ?string
    {
        return $this->parentCategoryName;
    }

    /**
     * @param string|null $parentCategoryName
     */
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