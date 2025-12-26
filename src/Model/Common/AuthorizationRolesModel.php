<?php

declare(strict_types = 1);

namespace App\Model\Common;

use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

class AuthorizationRolesModel
{
    /**
     * @var AuthorizationRoleModel[]
     */
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: new Model(type: AuthorizationRoleModel::class))
    )]
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
