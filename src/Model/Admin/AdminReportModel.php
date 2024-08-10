<?php

declare(strict_types=1);

namespace App\Model\Admin;

use App\Enums\ReportType;
use DateTime;
use OpenApi\Attributes as OA;

class AdminReportModel
{
    private string $id;
    private int $type;
    private int $dateAdd;
    private bool $accepted;
    private bool $denied;
    private ?string $description = null;
    private ?string $actionId = null;
    private ?string $email = null;
    private ?string $ip = null;
    private ?AdminUserModel $user = null;

    public function __construct(string $id, ReportType $type, DateTime $dateAdd, bool $accepted, bool $denied)
    {
        $this->id = $id;
        $this->type = $type->value;
        $this->dateAdd = $dateAdd->getTimestamp() * 1000;
        $this->accepted = $accepted;
        $this->denied = $denied;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    #[OA\Property(type: 'integer', enum: [1 => 'COMMENT', 2 => 'AUDIOBOOK_PROBLEM', 3 => 'CATEGORY_PROBLEM', 4 => 'SYSTEM_PROBLEM', 5 => 'USER_PROBLEM', 6 => 'SETTINGS_PROBLEM'])]
    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getActionId(): ?string
    {
        return $this->actionId;
    }

    public function setActionId(string $actionId): void
    {
        $this->actionId = $actionId;
    }

    public function getDateAdd(): int
    {
        return $this->dateAdd;
    }

    public function setDateAdd(DateTime $dateAdd): void
    {
        $this->dateAdd = $dateAdd->getTimestamp() * 1000;
    }

    public function getAccepted(): bool
    {
        return $this->accepted;
    }

    public function setAccepted(bool $accepted): void
    {
        $this->accepted = $accepted;
    }

    public function getDenied(): bool
    {
        return $this->denied;
    }

    public function setDenied(bool $denied): void
    {
        $this->denied = $denied;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }

    public function getUser(): ?AdminUserModel
    {
        return $this->user;
    }

    public function setUser(AdminUserModel $user): void
    {
        $this->user = $user;
    }
}
