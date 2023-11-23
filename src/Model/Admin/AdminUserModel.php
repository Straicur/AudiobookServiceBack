<?php

namespace App\Model\Admin;

use App\Enums\UserRoles;

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

    /**
     * @param string $id
     * @param bool $active
     * @param bool $banned
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     * @param \DateTime $dateCreated
     */
    public function __construct(string $id, bool $active, bool $banned, string $email, string $firstname, string $lastname, \DateTime $dateCreated, bool $deleted)
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

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return bool
     */
    public function isBanned(): bool
    {
        return $this->banned;
    }

    /**
     * @param bool $banned
     */
    public function setBanned(bool $banned): void
    {
        $this->banned = $banned;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    /**
     * @return int
     */
    public function getDateCreated(): int
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     */
    public function setDateCreated(\DateTime $dateCreated): void
    {
        $this->dateCreated = $dateCreated->getTimestamp() * 1000;
    }
    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function addRole(UserRoles $role): void
    {
        $this->roles[] = $role->value;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     */
    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }

}