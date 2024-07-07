<?php

namespace App\Query\Common;

use Symfony\Component\Validator\Constraints as Assert;

class AuthorizeQuery
{
    #[Assert\NotNull(message: 'Email is null')]
    #[Assert\NotBlank(message: 'Email is empty')]
    #[Assert\Email(message: "It's not an email")]
    private string $email;

    #[Assert\NotNull(message: 'Password is null')]
    #[Assert\NotBlank(message: 'Password is empty')]
    #[Assert\Type(type: 'string')]
    private string $password;

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(#[\SensitiveParameter] string $password): void
    {
        $this->password = $password;
    }
}
