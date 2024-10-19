<?php

namespace App\Entity;

use App\Repository\RegisterCodeRepository;
use App\ValueGenerator\RegisterCodeGenerator;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: RegisterCodeRepository::class)]
class RegisterCode
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, length: 512)]
    private string $code;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private DateTime $dateAdd;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $dateAccept = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $active;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    public function __construct(RegisterCodeGenerator $code, User $user)
    {
        $this->code = $code->generate();
        $this->dateAdd = new DateTime();
        $this->active = true;
        $this->user = $user;
    }


    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

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

    public function getDateAccept(): ?DateTime
    {
        return $this->dateAccept;
    }

    public function setDateAccept(DateTime $dateAccept): self
    {
        $this->dateAccept = $dateAccept;

        return $this;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
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
}
