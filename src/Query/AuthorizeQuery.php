<?php

namespace App\Query;

use App\Controller\AuthorizationController;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Authorize Query
 *
 * @see AuthorizationController::login()
 *
 */
class AuthorizeQuery
{
    #[Assert\NotNull(message: "Email is null")]
    #[Assert\NotBlank(message: "Email is empty")]
    #[Assert\Email(message: "It's not an email")]
    private string $email;

    #[Assert\NotNull(message: "Password is null")]
    #[Assert\NotBlank(message: "Password is empty")]
    #[Assert\Type(type: "string")]
    private string $password;

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
}