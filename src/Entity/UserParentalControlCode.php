<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Repository\UserParentalControlCodeRepository;
use App\ValueGenerator\ValueGeneratorInterface;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserParentalControlCodeRepository::class)]
class UserParentalControlCode
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: Types::STRING, length: 6)]
    private string $code;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private DateTime $dateAdd;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $active;

    public function __construct(User $user, ValueGeneratorInterface $userParentalControlCodeGenerator)
    {
        $this->user = $user;
        $this->code = $userParentalControlCodeGenerator->generate();
        $this->dateAdd = new DateTime();
        $this->active = true;
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

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(ValueGeneratorInterface $userParentalControlCodeGenerator): self
    {
        $this->code = $userParentalControlCodeGenerator->generate();

        return $this;
    }

    public function getDateAdd(): DateTime
    {
        return $this->dateAdd;
    }

    public function setDateAdd(DateTime $dateAdd): self
    {
        $this->dateAdd = $dateAdd;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}
