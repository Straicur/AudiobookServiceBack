<?php

namespace App\Annotation;

use App\Enums\UserRolesNames;

#[\Attribute(\Attribute::TARGET_METHOD)]
class AuthValidation
{
    private bool $checkAuthToken;

    private array $roles;

    public function __construct(bool $checkAuthToken, array $roles = [UserRolesNames::GUEST])
    {
        $this->checkAuthToken = $checkAuthToken;
        $this->roles = $roles;
    }

    public function isCheckAuthToken(): bool
    {
        return $this->checkAuthToken;
    }

    public function setCheckAuthToken(bool $checkAuthToken): void
    {
        $this->checkAuthToken = $checkAuthToken;
    }


    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }
}
