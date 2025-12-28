<?php

declare(strict_types = 1);

namespace App\Model\Serialization;

use DateTime;

class AdminReportsSearchModel
{
    public ?string $actionId = null;

    public ?string $desc = null;

    public ?string $email = null;

    public ?string $ip = null;

    public ?int $type = null;

    public ?bool $user = null;

    public ?bool $accepted = null;

    public ?bool $denied = null;

    public ?DateTime $dateFrom = null;

    public ?DateTime $dateTo = null;

    public ?int $order = null;

    public function getActionId(): ?string
    {
        return $this->actionId;
    }

    public function setActionId(?string $actionId): void
    {
        $this->actionId = $actionId;
    }

    public function getDesc(): ?string
    {
        return $this->desc;
    }

    public function setDesc(?string $desc): void
    {
        $this->desc = $desc;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): void
    {
        $this->ip = $ip;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): void
    {
        $this->type = $type;
    }

    public function getUser(): ?bool
    {
        return $this->user;
    }

    public function setUser(?bool $user): void
    {
        $this->user = $user;
    }

    public function getAccepted(): ?bool
    {
        return $this->accepted;
    }

    public function setAccepted(?bool $accepted): void
    {
        $this->accepted = $accepted;
    }

    public function getDenied(): ?bool
    {
        return $this->denied;
    }

    public function setDenied(?bool $denied): void
    {
        $this->denied = $denied;
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

    public function getOrder(): ?int
    {
        return $this->order;
    }

    public function setOrder(?int $order): void
    {
        $this->order = $order;
    }
}
