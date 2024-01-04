<?php

namespace App\Entity;

use App\Repository\TechnicalBreakRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TechnicalBreakRepository::class)]
class TechnicalBreak
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(type: 'boolean')]
    private bool $active;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $dateFrom;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $dateTo;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private User $user;

    /**
     * @param bool $active
     * @param User $user
     */
    public function __construct(bool $active, User $user)
    {
        $this->active = $active;
        $this->dateFrom = new \DateTime('Now');
        $this->user = $user;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getDateFrom(): \DateTime
    {
        return $this->dateFrom;
    }

    public function setDateFrom(\DateTime $dateFrom): self
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    public function getDateTo(): ?\DateTime
    {
        return $this->dateTo;
    }

    public function setDateTo(\DateTime $dateTo): self
    {
        $this->dateTo = $dateTo;

        return $this;
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
}
