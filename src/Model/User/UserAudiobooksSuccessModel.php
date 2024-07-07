<?php

declare(strict_types=1);

namespace App\Model\User;

use App\Model\ModelInterface;

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

    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    public function addCategory(UserCategoryModel $category): void
    {
        $this->categories[] = $category;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function getMaxPage(): int
    {
        return $this->maxPage;
    }

    public function setMaxPage(int $maxPage): void
    {
        $this->maxPage = $maxPage;
    }

}