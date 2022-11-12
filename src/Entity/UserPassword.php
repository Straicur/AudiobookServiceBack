<?php

namespace App\Entity;

use App\Repository\UserPasswordRepository;
use App\ValueGenerator\ValueGeneratorInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPasswordRepository::class)]
class UserPassword
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", nullable: false, onDelete: "CASCADE")]
    private User $user;

    #[ORM\Column(type: 'string', length: 512)]
    private string $password;

    /**
     * @param User $user
     * @param ValueGeneratorInterface $generator
     */
    public function __construct(User $user, ValueGeneratorInterface $generator)
    {
        $this->user = $user;
        $this->password = $generator->generate();
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
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
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param ValueGeneratorInterface $generator
     * @return $this
     */
    public function setPassword(ValueGeneratorInterface $generator): self
    {
        $this->password = $generator->generate();

        return $this;
    }
}
