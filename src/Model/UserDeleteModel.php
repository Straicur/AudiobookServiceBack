<?php

namespace App\Model;

class UserDeleteModel
{
    private string $id;
    private bool $active;
    private bool $banned;
    private string $email;
    private string $firstname;
    private bool $deleted;
    private bool $declined;
    private ?int $dateDeleted = null;

    /**
     * @param string $id
     * @param bool $active
     * @param bool $banned
     * @param string $email
     * @param string $firstname
     * @param bool $deleted
     * @param bool $declined
     */
    public function __construct(string $id, bool $active, bool $banned, string $email, string $firstname,bool $deleted ,bool $declined)
    {
        $this->id = $id;
        $this->active = $active;
        $this->banned = $banned;
        $this->email = $email;
        $this->firstname = $firstname;
        $this->deleted = $deleted;
        $this->declined = $declined;
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

    /**
     * @return bool
     */
    public function isDeclined(): bool
    {
        return $this->declined;
    }

    /**
     * @param bool $declined
     */
    public function setDeclined(bool $declined): void
    {
        $this->declined = $declined;
    }

    /**
     * @return int|null
     */
    public function getDateDeleted(): ?int
    {
        return $this->dateDeleted;
    }

    /**
     * @param \DateTime $dateDeleted
     */
    public function setDateDeleted(\DateTime $dateDeleted): void
    {
        $this->dateDeleted = $dateDeleted->getTimestamp();
    }

}