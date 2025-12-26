<?php

declare(strict_types = 1);

namespace App\Model\User;

use App\Model\ModelInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

class UserAudiobooksSuccessModel implements ModelInterface
{
    /**
     * @var UserCategoryModel[]
     */
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: new Model(type: UserCategoryModel::class))
    )]
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
