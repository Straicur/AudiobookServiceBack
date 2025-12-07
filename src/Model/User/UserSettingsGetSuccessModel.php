<?php

declare(strict_types = 1);

namespace App\Model\User;

use App\Model\ModelInterface;
use DateTime;

class UserSettingsGetSuccessModel implements ModelInterface
{
    private ?int $editableDate = null;

    private ?int $birthday = null;

    public function __construct(private string $email, private string $phoneNumber, private string $firstname, private string $lastname, private bool $edited) {}

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function isEdited(): bool
    {
        return $this->edited;
    }

    public function setEdited(bool $edited): void
    {
        $this->edited = $edited;
    }

    public function getEditableDate(): ?int
    {
        return $this->editableDate;
    }

    public function setEditableDate(DateTime $editableDate): void
    {
        $this->editableDate = $editableDate->getTimestamp() * 1000;
    }

    public function getBirthday(): ?int
    {
        return $this->birthday;
    }

    public function setBirthday(DateTime $birthday): void
    {
        $this->birthday = $birthday->getTimestamp() * 1000;
    }
}
