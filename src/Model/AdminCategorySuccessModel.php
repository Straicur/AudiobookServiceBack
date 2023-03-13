<?php

namespace App\Model;

class AdminCategorySuccessModel implements ModelInterface
{
    private string $id;
    private string $name;
    private bool $active;
    private ?string $parentCategoryName;

    /**
     * @param string $id
     * @param string $name
     * @param bool $active
     * @param string|null $parentCategoryName
     */
    public function __construct( string $id, string $name, bool $active, ?string $parentCategoryName = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->active = $active;
        $this->parentCategoryName = $parentCategoryName;
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

}