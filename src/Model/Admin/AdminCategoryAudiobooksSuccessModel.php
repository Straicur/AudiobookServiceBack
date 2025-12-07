<?php

declare(strict_types = 1);

namespace App\Model\Admin;

use App\Model\ModelInterface;

class AdminCategoryAudiobooksSuccessModel implements ModelInterface
{
    /**
     * @var AdminCategoryAudiobookModel[]
     */
    private array $audiobooks = [];

    private int $page;

    private int $limit;

    private int $maxPage;

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

    /**
     * @return AdminCategoryAudiobookModel[]
     */
    public function getAudiobooks(): array
    {
        return $this->audiobooks;
    }

    /**
     * @param AdminCategoryAudiobookModel[] $audiobooks
     */
    public function setAudiobooks(array $audiobooks): void
    {
        $this->audiobooks = $audiobooks;
    }

    public function addAudiobook(AdminCategoryAudiobookModel $audiobook): void
    {
        $this->audiobooks[] = $audiobook;
    }
}
