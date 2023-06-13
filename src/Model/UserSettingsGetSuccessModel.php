<?php

namespace App\Model;

class UserSettingsGetSuccessModel implements \App\Model\ModelInterface
{
    private string $phoneNumber;
    private string $firstname;
    private string $lastname;

    /**
     * @param string $phoneNumber
     * @param string $firstname
     * @param string $lastname
     */
    public function __construct(string $phoneNumber, string $firstname, string $lastname)
    {
        $this->phoneNumber = $phoneNumber;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
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

}