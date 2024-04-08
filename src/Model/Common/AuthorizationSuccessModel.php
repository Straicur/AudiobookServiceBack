<?php

declare(strict_types=1);

namespace App\Model\Common;

use App\Model\ModelInterface;

/**
 * AuthorizationSuccessModel
 *
 */
class AuthorizationSuccessModel implements ModelInterface
{
    private string $token;
    private AuthorizationRolesModel $roles;

    private bool $isAdmin;

    /**
     * @param string $token
     * @param AuthorizationRolesModel $roles
     * @param bool $isAdmin
     */
    public function __construct(string $token, AuthorizationRolesModel $roles,bool $isAdmin)
    {
        $this->token = $token;
        $this->roles = $roles;
        $this->isAdmin = $isAdmin;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    static function getModel(): array
    {
        return (array)AuthorizationSuccessModel::class;
    }

    /**
     * @return AuthorizationRolesModel
     */
    public function getRoles(): AuthorizationRolesModel
    {
        return $this->roles;
    }

    /**
     * @param AuthorizationRolesModel $roles
     */
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