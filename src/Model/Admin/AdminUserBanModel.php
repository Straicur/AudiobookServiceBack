<?php

declare(strict_types = 1);

namespace App\Model\Admin;

use App\Enums\UserBanType;
use DateTime;

class AdminUserBanModel
{
    private int $dateFrom;

    private int $dateTo;

    private int $type;

    public function __construct(DateTime $dateFrom, DateTime $dateTo, UserBanType $type)
    {
        $this->dateFrom = $dateFrom->getTimestamp() * 1000;
        $this->dateTo = $dateTo->getTimestamp() * 1000;
        $this->type = $type->value;
    }

    public function getDateFrom(): int
    {
        return $this->dateFrom;
    }

    public function setDateFrom(DateTime $dateFrom): void
    {
        $this->dateFrom = $dateFrom->getTimestamp() * 1000;
    }

    public function getDateTo(): int
    {
        return $this->dateTo;
    }

    public function setDateTo(DateTime $dateTo): void
    {
        $this->dateTo = $dateTo->getTimestamp() * 1000;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(UserBanType $type): void
    {
        $this->type = $type->value;
    }
}
