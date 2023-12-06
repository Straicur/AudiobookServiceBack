<?php

namespace App\Model\User;

use App\Model\Error\ModelInterface;

class UserAudiobooksSearchSuccessModel implements ModelInterface
{
    /**
     * @var UserAudiobookDetailModel[]
     */
    private array $audiobooks = [];

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

}