<?php

declare(strict_types = 1);

namespace App\Query\User;

use Symfony\Component\Validator\Constraints as Assert;

class UserSettingsChangeQuery
{
    #[Assert\NotNull(message: 'PhoneNumber is null')]
    #[Assert\NotBlank(message: 'PhoneNumber is empty')]
    #[Assert\Type(type: 'string')]
    private string $phoneNumber;

    #[Assert\NotNull(message: 'FirstName is null')]
    #[Assert\NotBlank(message: 'FirstName is empty')]
    #[Assert\Type(type: 'string')]
    private string $firstName;

    #[Assert\NotNull(message: 'LastName is null')]
    #[Assert\NotBlank(message: 'LastName is empty')]
    #[Assert\Type(type: 'string')]
    private string $lastName;

    #[Assert\NotNull(message: 'Code is null')]
    #[Assert\NotBlank(message: 'Code is empty')]
    #[Assert\Type(type: 'string')]
    private string $code;

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
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
