<?php

namespace App\Entity;

use App\Enums\ReportType;
use App\Repository\ReportRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ReportRepository::class)]
class Report
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(type: 'integer')]
    private int $type;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $dateAdd;

    #[ORM\Column(type: 'boolean')]
    private bool $accepted;

    #[ORM\Column(type: 'boolean')]
    private bool $denied;
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?Uuid $actionId = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $ip = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $email = null;
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $user = null;

    /**
     * @param ReportType $type
     */
    public function __construct(ReportType $type)
    {
        $this->type = $type->value;
        $this->accepted = false;
        $this->denied = false;
        $this->dateAdd = new \DateTime();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getType(): ReportType
    {
        return match ($this->type) {
            1 => ReportType::COMMENT,
            2 => ReportType::AUDIOBOOK_PROBLEM,
            3 => ReportType::CATEGORY_PROBLEM,
            4 => ReportType::SYSTEM_PROBLEM,
            5 => ReportType::USER_PROBLEM,
            6 => ReportType::SETTINGS_PROBLEM,
        };
    }

    public function setType(ReportType $type): self
    {
        $this->type = $type->value;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getActionId(): ?Uuid
    {
        return $this->actionId;
    }

    public function setActionId(Uuid $actionId): self
    {
        $this->actionId = $actionId;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }

    public function getDateAdd(): \DateTime
    {
        return $this->dateAdd;
    }

    public function setDateAdd(\DateTime $dateAdd): void
    {
        $this->dateAdd = $dateAdd;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAccepted(): bool
    {
        return $this->accepted;
    }

    public function setAccepted(bool $accepted): self
    {
        $this->accepted = $accepted;

        return $this;
    }

    public function getDenied(): bool
    {
        return $this->denied;
    }

    public function setDenied(bool $denied): self
    {
        $this->denied = $denied;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

}
