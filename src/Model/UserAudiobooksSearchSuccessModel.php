<?php

namespace App\Model;

class UserAudiobooksSearchSuccessModel implements ModelInterface
{
    /**
     * @var UserAudiobookDetailModel[]
     */
    private array $audiobooks = [];

    private int $page;

    private int $limit;

    private int $maxPage;

    /**
     * @return UserAudiobookDetailModel[]
     */
    public function getAudiobooks(): array
    {
        return $this->audiobooks;
    }

    /**
     * @param array $audiobooks
     */
    public function setAudiobooks(array $audiobooks): void
    {
        $this->audiobooks = $audiobooks;
    }

    public function addAudiobook(UserAudiobookDetailModel $audiobook): void
    {
        $this->audiobooks[] = $audiobook;
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