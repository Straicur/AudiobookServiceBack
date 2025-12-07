<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private DateTime $dateCreate;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $active;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $banned;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $bannedTo = null;

    #[ORM\OneToOne(targetEntity: UserInformation::class, mappedBy: 'user', cascade: ['persist'])]
    private ?UserInformation $userInformation;

    #[ORM\OneToOne(targetEntity: UserSettings::class, mappedBy: 'user', cascade: ['persist'])]
    private ?UserSettings $userSettings = null;

    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'users', cascade: ['persist'])]
    private Collection $roles;

    #[ORM\OneToOne(targetEntity: MyList::class, mappedBy: 'user', cascade: ['persist'])]
    private ?MyList $myList;

    #[ORM\OneToOne(targetEntity: ProposedAudiobooks::class, mappedBy: 'user', cascade: ['persist'])]
    private ?ProposedAudiobooks $proposedAudiobooks = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $edited;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $editableDate = null;

    #[ORM\ManyToMany(targetEntity: Notification::class, mappedBy: 'users')]
    private Collection $notifications;

    public function __construct()
    {
        $this->dateCreate = new DateTime();
        $this->active = false;
        $this->banned = false;
        $this->userInformation = null;
        $this->roles = new ArrayCollection();
        $this->myList = null;
        $this->edited = false;
        $this->notifications = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): User
    {
        $this->id = $id;

        return $this;
    }

    public function getDateCreate(): DateTime
    {
        return $this->dateCreate;
    }

    public function setDateCreate(DateTime $dateCreate): User
    {
        $this->dateCreate = $dateCreate;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): User
    {
        $this->active = $active;

        return $this;
    }

    public function isBanned(): bool
    {
        return $this->banned;
    }

    public function setBanned(bool $banned): User
    {
        $this->banned = $banned;

        return $this;
    }

    public function getUserInformation(): UserInformation
    {
        return $this->userInformation;
    }

    public function setUserInformation(UserInformation $userInformation): static
    {
        // set the owning side of the relation if necessary
        if ($userInformation->getUser() !== $this) {
            $userInformation->setUser($this);
        }

        $this->userInformation = $userInformation;

        return $this;
    }

    public function getUserSettings(): UserSettings
    {
        return $this->userSettings;
    }

    public function setUserSettings(UserSettings $userSettings): self
    {
        // set the owning side of the relation if necessary
        if ($userSettings->getUser() !== $this) {
            $userSettings->setUser($this);
        }

        $this->userSettings = $userSettings;

        return $this;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        $this->roles->removeElement($role);

        return $this;
    }

    public function getMyList(): MyList
    {
        return $this->myList;
    }

    public function setMyList(MyList $myList): self
    {
        if ($myList->getUser() !== $this) {
            $myList->setUser($this);
        }

        $this->myList = $myList;

        return $this;
    }

    public function getProposedAudiobooks(): ProposedAudiobooks
    {
        return $this->proposedAudiobooks;
    }

    public function setProposedAudiobooks(ProposedAudiobooks $proposedAudiobooks): self
    {
        if ($proposedAudiobooks->getUser() !== $this) {
            $proposedAudiobooks->setUser($this);
        }

        $this->proposedAudiobooks = $proposedAudiobooks;

        return $this;
    }

    public function getEdited(): bool
    {
        return $this->edited;
    }

    public function setEdited(bool $edited): self
    {
        $this->edited = $edited;

        return $this;
    }

    public function getEditableDate(): ?DateTime
    {
        return $this->editableDate;
    }

    public function setEditableDate(DateTime $editableDate): self
    {
        $this->editableDate = $editableDate;

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->addUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            $notification->removeUser($this);
        }

        return $this;
    }

    public function getBannedTo(): ?DateTime
    {
        return $this->bannedTo;
    }

    public function setBannedTo(DateTime $bannedTo): self
    {
        $this->bannedTo = $bannedTo;

        return $this;
    }
}
