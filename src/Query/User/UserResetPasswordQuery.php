<?php

declare(strict_types = 1);

namespace App\Query\User;

use Symfony\Component\Validator\Constraints as Assert;

class UserResetPasswordQuery
{
    #[Assert\NotNull(message: 'Email is null')]
    #[Assert\NotBlank(message: 'Email is empty')]
    #[Assert\Email]
    private string $email;

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}
