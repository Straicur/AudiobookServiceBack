<?php

namespace App\Entity;

use App\Repository\UserBanHistoryRepository;
use DateTime;
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

    #[ORM\Column(type: 'datetime')]
    private DateTime $dateFrom;

    #[ORM\Column(type: 'datetime')]
    private DateTime $dateTo;

    #[ORM\Column(type: 'integer',nullable: true)]
    private ?int $type = null;

    /**
     * @param User $user
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     */
    public function __construct(User $user, DateTime $dateFrom, DateTime $dateTo)
    {
        $this->user = $user;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }
}
