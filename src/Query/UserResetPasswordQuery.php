<?php

namespace App\Query;

use Symfony\Component\Validator\Constraints as Assert;

class UserResetPasswordQuery
{
    #[Assert\NotNull(message: "Email is null")]
    #[Assert\NotBlank(message: "Email is empty")]
    #[Assert\Email]
    private string $email;

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

}