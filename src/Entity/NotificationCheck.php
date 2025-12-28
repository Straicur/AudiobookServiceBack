<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Repository\NotificationCheckRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: NotificationCheckRepository::class)]
class NotificationCheck
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Notification::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Notification $notification;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private DateTime $dateWatched;

    public function __construct(User $user, Notification $notification)
    {
        $this->user = $user;
        $this->notification = $notification;
        $this->dateWatched = new DateTime();
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

    public function getNotification(): Notification
    {
        return $this->notification;
    }

    public function setNotification(Notification $notification): self
    {
        $this->notification = $notification;

        return $this;
    }

    public function getDateWatched(): DateTime
    {
        return $this->dateWatched;
    }

    public function setDateWatched(DateTime $dateWatched): self
    {
        $this->dateWatched = $dateWatched;

        return $this;
    }
}
