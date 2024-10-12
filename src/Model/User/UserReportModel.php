<?php

declare(strict_types=1);

namespace App\Model\User;

use App\Enums\ReportType;
use DateTime;
use OpenApi\Attributes as OA;

class UserReportModel
{
    private string $id;
    private int $type;
    private int $dateAdd;
    private bool $accepted;
    private bool $denied;
    private ?string $description = null;
    private ?string $comment = null;
    private ?string $answer = null;
    private ?int $settleDate = null;

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

    #[OA\Property(type: 'integer', enum: [
        1 => 'COMMENT',
        2 => 'AUDIOBOOK_PROBLEM',
        3 => 'CATEGORY_PROBLEM',
        4 => 'SYSTEM_PROBLEM',
        5 => 'USER_PROBLEM',
        6 => 'SETTINGS_PROBLEM',
        7 => 'RECRUITMENT_REQUEST',
        8 => 'OTHER',
    ])]
    public function getType(): int
    {
        return $this->type;
    }

    public function setType(ReportType $type): void
    {
        $this->type = $type->value;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDateAdd(): int
    {
        return $this->dateAdd;
    }

    public function setDateAdd(int $dateAdd): void
    {
        $this->dateAdd = $dateAdd;
    }

    public function isAccepted(): bool
    {
        return $this->accepted;
    }

    public function setAccepted(bool $accepted): void
    {
        $this->accepted = $accepted;
    }

    public function isDenied(): bool
    {
        return $this->denied;
    }

    public function setDenied(bool $denied): void
    {
        $this->denied = $denied;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): void
    {
        $this->answer = $answer;
    }

    public function getSettleDate(): ?int
    {
        return $this->settleDate;
    }

    public function setSettleDate(DateTime $settleDate): void
    {
        $this->settleDate = $settleDate->getTimestamp() * 1000;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }
}
