<?php

declare(strict_types = 1);

namespace App\Model\Admin;

use App\Model\ModelInterface;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

class AdminUsersSuccessModel implements ModelInterface
{
    /**
     * @var AdminUserModel[]
     */
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: new Model(type: AdminUserModel::class))
    )]
    private array $users = [];

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
     * @return AdminUserModel[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * @param AdminUserModel[] $users
     */
    public function setUsers(array $users): void
    {
        $this->users = $users;
    }

    public function addUser(AdminUserModel $user): void
    {
        $this->users[] = $user;
    }
}
