<?php

declare(strict_types=1);

namespace App\Model\Common;

use App\Model\ModelInterface;

class AuthorizationSuccessModel implements ModelInterface
{
    private string $token;
    private AuthorizationRolesModel $roles;

    private bool $isAdmin;

    public function __construct(string $token, AuthorizationRolesModel $roles, bool $isAdmin)
    {
        $this->token = $token;
        $this->roles = $roles;
        $this->isAdmin = $isAdmin;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getRoles(): AuthorizationRolesModel
    {
        return $this->roles;
    }

    public function setRoles(AuthorizationRolesModel $roles): void
    {
        $this->roles = $roles;
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool $isAdmin): void
    {
        $this->isAdmin = $isAdmin;
    }
}
