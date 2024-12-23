<?php

namespace App\Entity;

use App\Repository\UserPasswordRepository;
use App\ValueGenerator\ValueGeneratorInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPasswordRepository::class)]
class UserPassword
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: Types::STRING, length: 512)]
    private string $password;

    public function __construct(User $user, ValueGeneratorInterface $generator)
    {
        $this->user = $user;
        $this->password = $generator->generate();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(ValueGeneratorInterface $generator): static
    {
        $this->password = $generator->generate();

        return $this;
    }
}
