<?php

declare(strict_types = 1);

namespace App\Model\Admin;

use DateTime;

class AdminTechnicalBreakModel
{
    public int $dateFrom;

    public ?int $dateTo = null;

    public function __construct(public string $id, public bool $active, DateTime $dateFrom, public string $user)
    {
        $this->dateFrom = $dateFrom->getTimestamp() * 1000;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getDateFrom(): int
    {
        return $this->dateFrom;
    }

    public function setDateFrom(DateTime $dateFrom): void
    {
        $this->dateFrom = $dateFrom->getTimestamp() * 1000;
    }

    public function getDateTo(): ?int
    {
        return $this->dateTo;
    }

    public function setDateTo(DateTime $dateTo): void
    {
        $this->dateTo = $dateTo->getTimestamp() * 1000;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): void
    {
        $this->user = $user;
    }
}
