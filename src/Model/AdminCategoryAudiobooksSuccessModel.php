<?php

namespace App\Model;

class AdminCategoryAudiobooksSuccessModel implements ModelInterface
{
    /**
     * @var AdminCategoryAudiobookModel[]
     */
    private array $audiobooks = [];

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

    public function addAudiobook(AdminCategoryAudiobookModel $audiobook)
    {
        $this->audiobooks[] = $audiobook;
    }
}