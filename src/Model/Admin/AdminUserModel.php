<?php

declare(strict_types=1);

namespace App\Model\Admin;

use App\Enums\UserRoles;
use DateTime;

class AdminUserModel
{
    private string $id;
    private bool $active;
    private bool $banned;
    private string $email;
    private string $firstname;
    private string $lastname;
    private int $dateCreated;
    private array $roles = [];
    private bool $deleted;
    private ?string $phoneNumber = null;
    private ?AdminUserBanModel $userBan = null;

    public function __construct(string $id, bool $active, bool $banned, string $email, string $firstname, string $lastname, DateTime $dateCreated, bool $deleted)
    {
        $this->id = $id;
        $this->active = $active;
        $this->banned = $banned;
        $this->email = $email;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->dateCreated = $dateCreated->getTimestamp() * 1000;
        $this->deleted = $deleted;
    }

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

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function getDateCreated(): int
    {
        return $this->dateCreated;
    }

    public function setDateCreated(DateTime $dateCreated): void
    {
        $this->dateCreated = $dateCreated->getTimestamp() * 1000;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function addRole(UserRoles $role): void
    {
        $this->roles[] = $role->value;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getUserBan(): ?AdminUserBanModel
    {
        return $this->userBan;
    }

    public function setUserBan(AdminUserBanModel $userBan): void
    {
        $this->userBan = $userBan;
    }
}
