<?php

namespace App\Model\Admin;

use App\Model\Error\ModelInterface;

class AdminUserSystemRolesSuccessModel implements ModelInterface
{

    private array $roles = [];

    /**
     * @return SystemRoleModel[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function addRole(SystemRoleModel $role)
    {
        $this->roles[] = $role;
    }
}