<?php

namespace App\Entity;

use App\Repository\UserInformationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserInformationRepository::class)]
class UserInformation
{
    #[ORM\Id]
    #[ORM\OneToOne(inversedBy: 'userInformation', targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", nullable: false, onDelete: "CASCADE")]
    private User $user;

    #[ORM\Column(type: 'string', length: 510, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 16, unique: true)]
    private string $phoneNumber;

    #[ORM\Column(type: 'string', length: 255)]
    private string $firstname;

    #[ORM\Column(type: 'string', length: 255)]
    private string $lastname;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $birthday = null;

    /**
     * @param User $user
     * @param string $email
     * @param string $phoneNumber
     * @param string $firstname
     * @param string $lastname
     */
    public function __construct(User $user, string $email, string $phoneNumber, string $firstname, string $lastname)
    {
        $this->user = $user;
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
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
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
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
     * @return $this
     */
    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
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
     * @return $this
     */
    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
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
     * @return $this
     */
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getBirthday(): ?\DateTime
    {
        return $this->birthday;
    }

    public function setBirthday(?\DateTime $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

}
