<?php

declare(strict_types = 1);

namespace App\Query\Common;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterConfirmSendQuery
{
    #[Assert\NotNull(message: 'Email is null')]
    #[Assert\NotBlank(message: 'Email is empty')]
    #[Assert\Email(message: "It's not an email")]
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
