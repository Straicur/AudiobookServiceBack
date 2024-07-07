<?php

declare(strict_types=1);

namespace App\Model\Admin;

use App\Model\ModelInterface;

class AdminUserSystemRolesSuccessModel implements ModelInterface
{

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