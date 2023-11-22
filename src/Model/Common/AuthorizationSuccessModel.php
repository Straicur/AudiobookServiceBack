<?php

namespace App\Model\Common;

use App\Model\Error\ModelInterface;

/**
 * AuthorizationSuccessModel
 *
 */
class AuthorizationSuccessModel implements ModelInterface
{
    private string $token;

    private AuthorizationRolesModel $roles;

    /**
     * @param string $token
     * @param AuthorizationRolesModel $roles
     */
    public function __construct(string $token, AuthorizationRolesModel $roles)
    {
        $this->token = $token;
        $this->roles = $roles;
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

}