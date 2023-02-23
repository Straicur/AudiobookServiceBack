<?php

namespace App\Model;

class AdminCategorySuccessModel implements ModelInterface
{
    private string $name;
    private bool $active;
    private ?string $parentCategoryName;

    /**
     * @param string $name
     * @param bool $active
     * @param string|null $parentCategoryName
     */
    public function __construct( string $name, bool $active, ?string $parentCategoryName = null)
    {
        $this->name = $name;
        $this->active = $active;
        $this->parentCategoryName = $parentCategoryName;
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

}