<?php

declare(strict_types = 1);

namespace App\Model\User;

use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

class UserCategoryModel
{
    /**
     * @var UserAudiobookModel[]
     */
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: new Model(type: UserAudiobookModel::class))
    )]
    private array $audiobooks = [];

    public function __construct(private string $name, private string $categoryKey) {}

    /**
     * @return UserAudiobookModel[]
     */
    public function getAudiobooks(): array
    {
        return $this->audiobooks;
    }

    public function setAudiobooks(array $audiobooks): void
    {
        $this->audiobooks = $audiobooks;
    }

    public function addAudiobook(UserAudiobookModel $audiobook): void
    {
        $this->audiobooks[] = $audiobook;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCategoryKey(): string
    {
        return $this->categoryKey;
    }

    public function setCategoryKey(string $categoryKey): void
    {
        $this->categoryKey = $categoryKey;
    }
}
