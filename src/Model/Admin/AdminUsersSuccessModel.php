<?php

declare(strict_types=1);

namespace App\Model\Admin;

use App\Model\ModelInterface;

class AdminUsersSuccessModel implements ModelInterface
{
    /**
     * @var AdminUserModel[]
     */
    private array $users = [];

    private int $page;

    private int $limit;

    private int $maxPage;

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

    public function addUser(AdminUserModel $user)
    {
        $this->users[] = $user;
    }

}