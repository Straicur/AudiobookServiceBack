<?php

namespace App\Entity;

use App\Enums\NotificationType;
use App\Repository\NotificationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    private ?int $type;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $dateAdd;

    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?Uuid $actionId = null;

    #[ORM\Column(type: 'text')]
    private ?string $metaData;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'notifications')]
    private Collection $users;

    #[ORM\Column(type: 'boolean')]
    private bool $deleted;

    #[ORM\OneToMany(mappedBy: 'notification', targetEntity: NotificationCheck::class)]
    private Collection $notificationChecks;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $dateDeleted;

    public function __construct()
    {
        $this->dateAdd = new \DateTime('now');
        $this->users = new ArrayCollection();
        $this->deleted = false;
        $this->notificationChecks = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getType(): NotificationType
    {
        return match ($this->type) {
            1 => NotificationType::NORMAL,
            2 => NotificationType::ADMIN,
            3 => NotificationType::PROPOSED,
            4 => NotificationType::NEW_CATEGORY,
            5 => NotificationType::NEW_AUDIOBOOK,
            6 => NotificationType::USER_DELETE_DECLINE,
        };
    }

    public function setType(NotificationType $type): self
    {
        $this->type = $type->value;

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

    public function getActionId(): ?Uuid
    {
        return $this->actionId;
    }

    public function setActionId(Uuid $actionId): self
    {
        $this->actionId = $actionId;

        return $this;
    }

    public function getMetaData(): array
    {
        return json_decode($this->metaData, true);
    }

    public function setMetaData(string $metaData): self
    {
        $this->metaData = $metaData;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->users->removeElement($user);

        return $this;
    }

    public function getDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getNotificationChecks(): Collection
    {
        return $this->notificationChecks;
    }

    /**
     * @param Collection $notificationChecks
     */
    public function setNotificationChecks(Collection $notificationChecks): void
    {
        $this->notificationChecks = $notificationChecks;
    }

    public function getDateDeleted(): ?\DateTime
    {
        return $this->dateDeleted;
    }

    public function setDateDeleted(\DateTime $dateDeleted): self
    {
        $this->dateDeleted = $dateDeleted;

        return $this;
    }

}
