<?php

namespace App\Entity;

use App\Repository\UserEditRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserEditRepository::class)]
class UserEdit
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: 'boolean')]
    private bool $edited;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $editableDate = null;

    #[ORM\Column(type: 'integer')]
    private int $type;

    /**
     * @param User $user
     * @param bool $edited
     * @param int $type
     */
    public function __construct(User $user, bool $edited, int $type)
    {
        $this->user = $user;
        $this->edited = $edited;
        $this->type = $type;
    }

    /**
     * @return Uuid
     */
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

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }
}
