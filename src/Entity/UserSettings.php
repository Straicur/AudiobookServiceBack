<?php

namespace App\Entity;

use App\Repository\UserSettingsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserSettingsRepository::class)]
class UserSettings
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'userSettings')]
    #[ORM\JoinColumn(name: 'user_id', nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $normal;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $admin;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->normal = true;
        $this->admin = false;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function isNormal(): bool
    {
        return $this->normal;
    }

    public function setNormal(bool $normal): void
    {
        $this->normal = $normal;
    }

    public function isAdmin(): bool
    {
        return $this->admin;
    }

    public function setAdmin(bool $admin): self
    {
        $this->admin = $admin;

        return $this;
    }
}
