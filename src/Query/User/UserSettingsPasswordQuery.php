<?php

declare(strict_types = 1);

namespace App\Query\User;

use Symfony\Component\Validator\Constraints as Assert;

class UserSettingsPasswordQuery
{
    #[Assert\NotNull(message: 'OldPassword is null')]
    #[Assert\NotBlank(message: 'OldPassword is empty')]
    #[Assert\Type(type: 'string')]
    private string $oldPassword;

    #[Assert\NotNull(message: 'NewPassword is null')]
    #[Assert\NotBlank(message: 'NewPassword is empty')]
    #[Assert\Type(type: 'string')]
    private string $newPassword;

    #[Assert\NotNull(message: 'Code is null')]
    #[Assert\NotBlank(message: 'Code is empty')]
    #[Assert\Type(type: 'string')]
    private string $code;

    public function getOldPassword(): string
    {
        return $this->oldPassword;
    }

    public function setOldPassword(string $oldPassword): void
    {
        $this->oldPassword = $oldPassword;
    }

    public function getNewPassword(): string
    {
        return $this->newPassword;
    }

    public function setNewPassword(string $newPassword): void
    {
        $this->newPassword = $newPassword;
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
