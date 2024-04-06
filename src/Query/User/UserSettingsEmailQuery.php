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

    /**
     * @return string
     */
    public function getNewEmail(): string
    {
        return $this->newEmail;
    }

    /**
     * @param string $newEmail
     */
    public function setNewEmail(string $newEmail): void
    {
        $this->newEmail = $newEmail;
    }

    /**
     * @return string
     */
    public function getOldEmail(): string
    {
        return $this->oldEmail;
    }

    /**
     * @param string $oldEmail
     */
    public function setOldEmail(string $oldEmail): void
    {
        $this->oldEmail = $oldEmail;
    }

}