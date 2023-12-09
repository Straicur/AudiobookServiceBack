<?php

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * Annotation class for @AuthValidation()
 *
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"METHOD"})
 *
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class AuthValidation
{
    private bool $checkAuthToken;

    private array $roles;

    /**
     * @param bool $checkAuthToken
     * @param array $roles
     */
    public function __construct(bool $checkAuthToken, array $roles = ["Guest"])
    {
        $this->checkAuthToken = $checkAuthToken;
        $this->roles = $roles;
    }

    /**
     * @return bool
     */
    public function isCheckAuthToken(): bool
    {
        return $this->checkAuthToken;
    }

    /**
     * @param bool $checkAuthToken
     */
    public function setCheckAuthToken(bool $checkAuthToken): void
    {
        $this->checkAuthToken = $checkAuthToken;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array|string[] $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }
}