<?php

declare(strict_types = 1);

namespace App\Model\User;

use App\Model\ModelInterface;

class UserProposedAudiobooksSuccessModel implements ModelInterface
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

    public function setAudiobooks(array $audiobooks): void
    {
        $this->audiobooks = $audiobooks;
    }

    public function addAudiobook(UserAudiobookDetailModel $audiobook): void
    {
        $this->audiobooks[] = $audiobook;
    }
}
