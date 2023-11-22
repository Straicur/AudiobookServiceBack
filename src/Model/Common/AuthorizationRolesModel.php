<?php

namespace App\Model\Common;

use App\Model\Error\ModelInterface;

class AuthorizationRolesModel implements ModelInterface
{
    /**
     * @var AuthorizationRoleModel[]
     */
    private array $authorizationRoleModels;

    /**
     * @return AuthorizationRoleModel[]
     */
    public function getAuthorizationRoleModels(): array
    {
        return $this->authorizationRoleModels;
    }

    /**
     * @param AuthorizationRoleModel[] $authorizationRoleModels
     */
    public function setAuthorizationRoleModels(array $authorizationRoleModels): void
    {
        $this->authorizationRoleModels = $authorizationRoleModels;
    }

    public function addAuthorizationRoleModel(AuthorizationRoleModel $authorizationRoleModel): void
    {
        $this->authorizationRoleModels[] = $authorizationRoleModel;
    }
}