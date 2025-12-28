<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Enums\UserBanType;
use App\Repository\UserBanHistoryRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserBanHistoryRepository::class)]
class UserBanHistory
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private DateTime $dateFrom;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private DateTime $dateTo;

    #[ORM\Column(type: Types::INTEGER)]
    private int $type;

    public function __construct(User $user, DateTime $dateFrom, DateTime $dateTo, UserBanType $type)
    {
        $this->user = $user;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->type = $type->value;
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

    public function getDateFrom(): DateTime
    {
        return $this->dateFrom;
    }

    public function setDateFrom(DateTime $dateFrom): self
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    public function getDateTo(): DateTime
    {
        return $this->dateTo;
    }

    public function setDateTo(DateTime $dateTo): self
    {
        $this->dateTo = $dateTo;

        return $this;
    }

    public function getType(): UserBanType
    {
        return match ($this->type) {
            1 => UserBanType::SPAM,
            2 => UserBanType::COMMENT,
            3 => UserBanType::STRANGE_BEHAVIOR,
            4 => UserBanType::MAX_LOGINS_BREAK,
        };
    }

    public function setType(UserBanType $type): self
    {
        $this->type = $type->value;

        return $this;
    }
}
