<?php

namespace App\Model\Admin;

use App\Model\Error\ModelInterface;

class AdminAudiobookCategoryModel implements ModelInterface
{
    private string $id;
    private string $name;
    private bool $active;
    private string $categoryKey;

    /**
     * @param string $id
     * @param string $name
     * @param bool $active
     * @param string $categoryKey
     */
    public function __construct(string $id, string $name, bool $active, string $categoryKey)
    {
        $this->id = $id;
        $this->name = $name;
        $this->active = $active;
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