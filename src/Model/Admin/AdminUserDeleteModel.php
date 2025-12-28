<?php

declare(strict_types = 1);

namespace App\Model\Admin;

use DateTime;

class AdminUserDeleteModel
{
    private ?int $dateDeleted = null;

    public function __construct(private string $id, private bool $active, private bool $banned, private string $email, private string $firstname, private bool $deleted, private bool $declined) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function isBanned(): bool
    {
        return $this->banned;
    }

    public function setBanned(bool $banned): void
    {
        $this->banned = $banned;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }

    public function isDeclined(): bool
    {
        return $this->declined;
    }

    public function setDeclined(bool $declined): void
    {
        $this->declined = $declined;
    }

    public function getDateDeleted(): ?int
    {
        return $this->dateDeleted;
    }

    public function setDateDeleted(DateTime $dateDeleted): void
    {
        $this->dateDeleted = $dateDeleted->getTimestamp() * 1000;
    }
}
