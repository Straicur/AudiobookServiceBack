<?php

namespace App\Model\User;

use App\Model\Error\ModelInterface;

class UserAudiobooksSuccessModel implements ModelInterface
{
    /**
     * @var UserCategoryModel[]
     */
    private array $categories = [];

    private int $page;

    private int $limit;

    private int $maxPage;

    /**
     * @return UserCategoryModel[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     */
    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    public function addCategory(UserCategoryModel $category): void
    {
        $this->categories[] = $category;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getMaxPage(): int
    {
        return $this->maxPage;
    }

    /**
     * @param int $maxPage
     */
    public function setMaxPage(int $maxPage): void
    {
        $this->maxPage = $maxPage;
    }

}