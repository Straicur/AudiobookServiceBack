<?php

declare(strict_types = 1);

namespace App\Model\User;

use App\Model\ModelInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

class UserAudiobooksSearchSuccessModel implements ModelInterface
{
    /**
     * @var UserAudiobookDetailModel[]
     */
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: new Model(type: UserAudiobookDetailModel::class))
    )]
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
