<?php

declare(strict_types=1);

namespace App\Model\Serialization;

use DateTime;

class AdminTechnicalBreaksSearchModel
{
    public ?string $nameOrLastname = null;
    public ?bool $active = null;
    public ?int $order = null;
    public ?DateTime $dateFrom = null;
    public ?DateTime $dateTo = null;

    public function getNameOrLastname(): ?string
    {
        return $this->nameOrLastname;
    }

    public function setNameOrLastname(?string $nameOrLastname): void
    {
        $this->nameOrLastname = $nameOrLastname;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): void
    {
        $this->active = $active;
    }

    public function getOrder(): ?int
    {
        return $this->order;
    }

    public function setOrder(?int $order): void
    {
        $this->order = $order;
    }

    public function getDateFrom(): ?DateTime
    {
        return $this->dateFrom;
    }

    public function setDateFrom(?string $dateFrom): void
    {
        if (DateTime::createFromFormat('d.m.Y', $dateFrom)) {
            $this->dateFrom = DateTime::createFromFormat('d.m.Y', $dateFrom);
        }
    }

    public function getDateTo(): ?DateTime
    {
        return $this->dateTo;
    }

    public function setDateTo(?string $dateTo): void
    {
        if (DateTime::createFromFormat('d.m.Y', $dateTo)) {
            $this->dateTo = DateTime::createFromFormat('d.m.Y', $dateTo);
        }
    }
}
