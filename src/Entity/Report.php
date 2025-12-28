<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Enums\ReportType;
use App\Repository\ReportRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ReportRepository::class)]
class Report
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(type: Types::INTEGER)]
    private int $type;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private DateTime $dateAdd;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $accepted;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $denied;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $actionId = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $ip = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $email = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $user = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $answer = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?UserBanHistory $banned = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $settleDate = null;

    public function __construct(ReportType $type)
    {
        $this->type = $type->value;
        $this->accepted = false;
        $this->denied = false;
        $this->dateAdd = new DateTime();
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
            7 => ReportType::RECRUITMENT_REQUEST,
            8 => ReportType::OTHER,
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

    public function getActionId(): ?string
    {
        return $this->actionId;
    }

    public function setActionId(string $actionId): self
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

    public function getDateAdd(): DateTime
    {
        return $this->dateAdd;
    }

    public function setDateAdd(DateTime $dateAdd): void
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

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(?string $answer): static
    {
        $this->answer = $answer;

        return $this;
    }

    public function getBanned(): ?UserBanHistory
    {
        return $this->banned;
    }

    public function setBanned(?UserBanHistory $banned): static
    {
        $this->banned = $banned;

        return $this;
    }

    public function getSettleDate(): ?DateTime
    {
        return $this->settleDate;
    }

    public function setSettleDate(DateTime $settleDate): static
    {
        $this->settleDate = $settleDate;

        return $this;
    }
}
