<?php

namespace App\Model;

class UserSettingsGetSuccessModel implements \App\Model\ModelInterface
{
    private string $email;
    private string $phoneNumber;
    private string $firstname;
    private string $lastname;
    private bool $edited;
    private ?int $editableDate = null;

    /**
     * @param string $email
     * @param string $phoneNumber
     * @param string $firstname
     * @param string $lastname
     * @param bool $edited
     * @param int|null $editableDate
     */
    public function __construct(string $email, string $phoneNumber, string $firstname, string $lastname, bool $edited, ?int $editableDate = null)
    {
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->edited = $edited;
        $this->editableDate = $editableDate;
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
     * @return bool
     */
    public function isEdited(): bool
    {
        return $this->edited;
    }

    /**
     * @param bool $edited
     */
    public function setEdited(bool $edited): void
    {
        $this->edited = $edited;
    }

    /**
     * @return int|null
     */
    public function getEditableDate(): ?int
    {
        return $this->editableDate;
    }

    /**
     * @param int $editableDate
     */
    public function setEditableDate(int $editableDate): void
    {
        $this->editableDate = $editableDate;
    }

}