<?php

namespace App\Entity;

use App\Repository\UserDeleteRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserDeleteRepository::class)]
class UserDelete
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $dateDeleted = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $deleted;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $declined;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->deleted = false;
        $this->declined = false;
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

    public function getDateDeleted(): ?DateTime
    {
        return $this->dateDeleted;
    }

    public function setDateDeleted(DateTime $dateDeleted): self
    {
        $this->dateDeleted = $dateDeleted;

        return $this;
    }

    public function getDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getDeclined(): bool
    {
        return $this->declined;
    }

    public function setDeclined(bool $declined): self
    {
        $this->declined = $declined;

        return $this;
    }
}
