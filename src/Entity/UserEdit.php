<?php

namespace App\Entity;

use App\Enums\UserEditType;
use App\Repository\UserEditRepository;
use App\ValueGenerator\ValueGeneratorInterface;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserEditRepository::class)]
class UserEdit
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $edited;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $editableDate = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $type;

    #[ORM\Column(type: Types::STRING, length: 8, nullable: true)]
    private ?string $code = null;

    public function __construct(User $user, bool $edited, UserEditType $type)
    {
        $this->user = $user;
        $this->edited = $edited;
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

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(UserEditType $type): self
    {
        $this->type = $type->value;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(ValueGeneratorInterface $userEditConfirmGenerator): static
    {
        $this->code = $userEditConfirmGenerator->generate();

        return $this;
    }
}
