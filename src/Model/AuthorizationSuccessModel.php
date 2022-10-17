<?php

namespace App\Model;

/**
 * AuthorizationSuccessModel
 *
 */
class AuthorizationSuccessModel implements ModelInterface
{
    private string $token;

    /**
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
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

    static function getModel():array{
        return (array)AuthorizationSuccessModel::class;
    }
}