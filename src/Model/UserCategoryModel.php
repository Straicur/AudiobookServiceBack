<?php

namespace App\Model;

class UserCategoryModel
{
    /**
     * @var UserAudiobookModel[]
     */
    private array $audiobooks = [];

    /**
     * @return UserAudiobookModel[]
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

    public function addAudiobook(UserAudiobookModel $audiobook): void
    {
        $this->audiobooks[] = $audiobook;
    }
}