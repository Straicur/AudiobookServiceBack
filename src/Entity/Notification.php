<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(type: 'integer')]
    private int $type;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $dateAdd;

    #[ORM\Column(type: 'boolean')]
    private bool $readStatus;

    #[ORM\Column(type: 'uuid')]
    private Uuid $actionId;

    #[ORM\Column(type: 'text')]
    private string $metaData;

    /**
     * @param int $type
     * @param User $user
     * @param Uuid $actionId
     * @param string $metaData
     */
    public function __construct(int $type, User $user, Uuid $actionId, string $metaData)
    {
        $this->type = $type;
        $this->user = $user;
        $this->dateAdd = new \DateTime('Now');
        $this->readStatus = false;
        $this->actionId = $actionId;
        $this->metaData = $metaData;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

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

    public function getDateAdd(): \DateTime
    {
        return $this->dateAdd;
    }

    public function setDateAdd(\DateTime $dateAdd): self
    {
        $this->dateAdd = $dateAdd;

        return $this;
    }

    public function getReadStatus(): bool
    {
        return $this->readStatus;
    }

    public function setReadStatus(bool $readStatus): self
    {
        $this->readStatus = $readStatus;

        return $this;
    }

    public function getActionId(): Uuid
    {
        return $this->actionId;
    }

    public function setActionId(Uuid $actionId): self
    {
        $this->actionId = $actionId;

        return $this;
    }

    public function getMetaData(): string
    {
        return $this->metaData;
    }

    public function setMetaData(string $metaData): self
    {
        $this->metaData = $metaData;

        return $this;
    }
}
