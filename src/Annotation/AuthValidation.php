<?php

declare(strict_types = 1);

namespace App\Annotation;

use App\Enums\UserRolesNames;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class AuthValidation
{
    /**
     * @param UserRolesNames[] $roles
     */
    public function __construct(private bool $checkAuthToken, private array $roles = [UserRolesNames::GUEST]) {}

    public function isCheckAuthToken(): bool
    {
        return $this->checkAuthToken;
    }

    public function setCheckAuthToken(bool $checkAuthToken): void
    {
        $this->checkAuthToken = $checkAuthToken;
    }

    /**
     * @return UserRolesNames[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }
}
