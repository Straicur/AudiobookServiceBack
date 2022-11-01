<?php

namespace App\Model;

class UserModel
{
    private string $id;
    private bool $active;
    private bool $banned;
    private string $email;
    private string $firstname;

    /**
     * @param string $id
     * @param bool $active
     * @param bool $banned
     * @param string $email
     * @param string $firstname
     */
    public function __construct(string $id, bool $active, bool $banned, string $email, string $firstname)
    {
        $this->id = $id;
        $this->active = $active;
        $this->banned = $banned;
        $this->email = $email;
        $this->firstname = $firstname;
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


}