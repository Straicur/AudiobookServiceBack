<?php

declare(strict_types = 1);

namespace App\Model\Admin;

use App\Model\ModelInterface;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

class AdminUserSystemRolesSuccessModel implements ModelInterface
{
    #[OA\Property(
        type: 'array',
        items: new OA\Items(ref: new Model(type: AdminSystemRoleModel::class))
    )]
    private array $roles = [];

    /**
     * @return AdminSystemRoleModel[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function addRole(AdminSystemRoleModel $role): void
    {
        $this->roles[] = $role;
    }
}
