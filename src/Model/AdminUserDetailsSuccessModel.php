<?php

namespace App\Model;

use App\Enums\UserRoles;

class AdminUserDetailsSuccessModel implements ModelInterface
{
    private string $id;
    private int $dateCreate;
    private bool $active;
    private bool $banned;
    private string $email;
    private string $phoneNumber;
    private string $firstname;
    private string $lastname;
    private array $roles = [];

    /**
     * @param string $id
     * @param \DateTime $dateCreate
     * @param bool $active
     * @param bool $banned
     * @param string $email
     * @param string $phoneNumber
     * @param string $firstname
     * @param string $lastname
     */
    public function __construct(string $id, \DateTime $dateCreate, bool $active, bool $banned, string $email, string $phoneNumber, string $firstname, string $lastname)
    {
        $this->id = $id;
        $this->dateCreate = $dateCreate->getTimestamp();
        $this->active = $active;
        $this->banned = $banned;
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
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
     * @return int
     */
    public function getDateCreate(): int
    {
        return $this->dateCreate;
    }

    /**
     * @param \DateTime $dateCreate
     */
    public function setDateCreate(\DateTime $dateCreate): void
    {
        $this->dateCreate = $dateCreate->getTimestamp();
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
    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber(string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
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

    public function addRole(UserRoles $role)
    {
        $this->roles[] = $role->value;
    }
}