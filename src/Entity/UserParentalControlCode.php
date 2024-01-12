<?php

namespace App\Entity;

use App\Repository\UserParentalControlCodeRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserParentalControlCodeRepository::class)]
class UserParentalControlCode
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: 'string', length: 6)]
    private string $code;

    #[ORM\Column(type: 'datetime')]
    private DateTime $dateAdd;

    #[ORM\Column(type: 'boolean')]
    private bool $used;

    /**
     * @param User $user
     * @param string $code
     */
    public function __construct(User $user, string $code)
    {
        $this->user = $user;
        $this->code = $code;
        $this->dateAdd = new DateTime("Now");
        $this->used = false;
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

    public function getUsed(): bool
    {
        return $this->used;
    }

    public function setUsed(bool $used): self
    {
        $this->used = $used;

        return $this;
    }
}
