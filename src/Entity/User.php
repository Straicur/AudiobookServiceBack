<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $dateCreate;

    #[ORM\Column(type: 'boolean')]
    private bool $active;

    #[ORM\Column(type: 'boolean')]
    private bool $banned;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserInformation::class, cascade: ['persist', 'remove'])]
    private ?UserInformation $userInformation;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserSettings::class, cascade: ['persist', 'remove'])]
    private UserSettings $userSettings;

    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'users', cascade: ['persist', 'remove'])]
    private Collection $roles;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: MyList::class, cascade: ['persist', 'remove'])]
    private ?MyList $myList;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: ProposedAudiobooks::class, cascade: ['persist', 'remove'])]
    private ?ProposedAudiobooks $proposedAudiobooks;

    #[ORM\Column(type: 'boolean')]
    private bool $edited;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $editableDate;

    public function __construct()
    {
        $this->dateCreate = new \DateTime("now");
        $this->active = false;
        $this->banned = false;
        $this->userInformation = null;
        $this->roles = new ArrayCollection();
        $this->myList = null;
        $this->edited = false;
    }

    /**
     * @return Uuid
     */
    public function getId(): Uuid
    {
        return $this->id;
    }

    /**
     * @param Uuid $id
     * @return User
     */
    public function setId(Uuid $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreate(): \DateTime
    {
        return $this->dateCreate;
    }

    /**
     * @param \DateTime $dateCreate
     * @return User
     */
    public function setDateCreate(\DateTime $dateCreate): self
    {
        $this->dateCreate = $dateCreate;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return User
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return bool
     */
    public function isBanned(): bool
    {
        return $this->banned;
    }

    /**
     * @param bool $banned
     * @return User
     */
    public function setBanned(bool $banned): self
    {
        $this->banned = $banned;
        return $this;
    }

    /**
     * @return UserInformation|null
     */
    public function getUserInformation(): ?UserInformation
    {
        return $this->userInformation;
    }

    /**
     * @param UserInformation $userInformation
     * @return $this
     */
    public function setUserInformation(UserInformation $userInformation): self
    {
        // set the owning side of the relation if necessary
        if ($userInformation->getUser() !== $this) {
            $userInformation->setUser($this);
        }

        $this->userInformation = $userInformation;

        return $this;
    }

    public function getUserSettings(): ?UserSettings
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

    public function getMyList(): ?MyList
    {
        return $this->myList;
    }

    public function setMyList(MyList $myList): self
    {
        // set the owning side of the relation if necessary
        if ($myList->getUser() !== $this) {
            $myList->setUser($this);
        }

        $this->myList = $myList;

        return $this;
    }

    public function getProposedAudiobooks(): ?ProposedAudiobooks
    {
        return $this->proposedAudiobooks;
    }

    public function setProposedAudiobooks(ProposedAudiobooks $proposedAudiobooks): self
    {
        // set the owning side of the relation if necessary
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

    public function getEditableDate(): ?\DateTime
    {
        return $this->editableDate;
    }

    public function setEditableDate(\DateTime $editableDate): self
    {
        $this->editableDate = $editableDate;

        return $this;
    }
}
