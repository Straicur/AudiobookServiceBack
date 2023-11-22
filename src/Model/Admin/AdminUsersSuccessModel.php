<?php

namespace App\Model\Admin;

use App\Model\Error\ModelInterface;
use App\Model\User\UserModel;

class AdminUsersSuccessModel implements ModelInterface
{
    /**
     * @var UserModel[]
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
     * @return UserModel[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * @param UserModel[] $users
     */
    public function setUsers(array $users): void
    {
        $this->users = $users;
    }

    public function addUser(UserModel $user)
    {
        $this->users[] = $user;
    }

}