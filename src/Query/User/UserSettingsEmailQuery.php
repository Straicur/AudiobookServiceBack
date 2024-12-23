<?php

namespace App\Query\User;

use Symfony\Component\Validator\Constraints as Assert;

class UserSettingsEmailQuery
{
    #[Assert\NotNull(message: 'NewEmail is null')]
    #[Assert\NotBlank(message: 'NewEmail is empty')]
    #[Assert\Email]
    private string $newEmail;

    #[Assert\NotNull(message: 'OldEmail is null')]
    #[Assert\NotBlank(message: 'OldEmail is empty')]
    #[Assert\Email]
    private string $oldEmail;

    #[Assert\NotNull(message: 'Code is null')]
    #[Assert\NotBlank(message: 'Code is empty')]
    #[Assert\Type(type: 'string')]
    private string $code;

    public function getNewEmail(): string
    {
        return $this->newEmail;
    }

    public function setNewEmail(string $newEmail): void
    {
        $this->newEmail = $newEmail;
    }

    public function getOldEmail(): string
    {
        return $this->oldEmail;
    }

    public function setOldEmail(string $oldEmail): void
    {
        $this->oldEmail = $oldEmail;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }
}
