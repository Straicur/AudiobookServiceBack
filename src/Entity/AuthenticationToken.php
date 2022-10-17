<?php

namespace App\Entity;

use App\Repository\AuthenticationTokenRepository;
use App\ValueGenerator\ValueGeneratorInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AuthenticationTokenRepository::class)]
class AuthenticationToken
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: 'string', length: 512)]
    private string $token;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $dateCreate;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $dateExpired;

    /**
     * @param User $user
     * @param ValueGeneratorInterface $tokenGenerator
     */
    public function __construct(User $user, ValueGeneratorInterface $tokenGenerator)
    {
        $this->user = $user;
        $this->token = $tokenGenerator->generate();
        $this->dateCreate = new \DateTime("now");
        $this->dateExpired = clone $this->dateCreate;
        $this->dateExpired->modify("+1 day");
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(ValueGeneratorInterface $tokenGenerator): self
    {
        $this->token = $tokenGenerator->generate();

        return $this;
    }

    public function getDateCreate(): ?\DateTimeInterface
    {
        return $this->dateCreate;
    }

    public function setDateCreate(\DateTimeInterface $dateCreate): self
    {
        $this->dateCreate = $dateCreate;

        return $this;
    }

    public function getDateExpired(): ?\DateTimeInterface
    {
        return $this->dateExpired;
    }

    public function setDateExpired(\DateTimeInterface $dateExpired): self
    {
        $this->dateExpired = $dateExpired;

        return $this;
    }
}
