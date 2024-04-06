<?php

namespace App\Entity;

use App\Repository\AuthenticationTokenRepository;
use App\ValueGenerator\ValueGeneratorInterface;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AuthenticationTokenRepository::class)]
class AuthenticationToken
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'string', length: 512)]
    private string $token;

    #[ORM\Column(type: 'datetime')]
    private DateTime $dateCreate;

    #[ORM\Column(type: 'datetime')]
    private DateTime $dateExpired;

    /**
     * @param User $user
     * @param ValueGeneratorInterface $tokenGenerator
     */
    public function __construct(User $user, ValueGeneratorInterface $tokenGenerator)
    {
        $this->user = $user;
        $this->token = $tokenGenerator->generate();
        $this->dateCreate = new DateTime();
        $this->dateExpired = (new DateTime())->modify('+4 hour');
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(ValueGeneratorInterface $tokenGenerator): self
    {
        $this->token = $tokenGenerator->generate();

        return $this;
    }

    public function getDateCreate(): DateTime
    {
        return $this->dateCreate;
    }

    public function setDateCreate(DateTime $dateCreate): self
    {
        $this->dateCreate = $dateCreate;

        return $this;
    }

    public function getDateExpired(): DateTime
    {
        return $this->dateExpired;
    }

    public function setDateExpired(DateTime $dateExpired): self
    {
        $this->dateExpired = $dateExpired;

        return $this;
    }
}
