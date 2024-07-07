<?php

declare(strict_types=1);

namespace App\Model\User;

use App\Model\ModelInterface;
use DateTime;

class UserSettingsGetSuccessModel implements ModelInterface
{
    private string $email;
    private string $phoneNumber;
    private string $firstname;
    private string $lastname;
    private bool $edited;
    private ?int $editableDate = null;

    public function __construct(string $email, string $phoneNumber, string $firstname, string $lastname, bool $edited)
    {
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->edited = $edited;
    }

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

}