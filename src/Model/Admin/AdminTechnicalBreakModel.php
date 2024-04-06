<?php

declare(strict_types=1);

namespace App\Model\Admin;

use DateTime;

class AdminTechnicalBreakModel
{
    public string $id;
    public bool $active;
    public int $dateFrom;
    public ?int $dateTo = null;
    public string $user;

    /**
     * @param string $id
     * @param bool $active
     * @param DateTime $dateFrom
     * @param string $user
     */
    public function __construct(string $id, bool $active, DateTime $dateFrom, string $user)
    {
        $this->id = $id;
        $this->active = $active;
        $this->dateFrom = $dateFrom->getTimestamp();
        $this->user = $user;
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
        $this->dateFrom = $dateFrom->getTimestamp();
    }

    public function getDateTo(): ?int
    {
        return $this->dateTo;
    }

    public function setDateTo(DateTime $dateTo): void
    {
        $this->dateTo = $dateTo->getTimestamp();
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