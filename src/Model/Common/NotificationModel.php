<?php

declare(strict_types = 1);

namespace App\Model\Common;

use App\Entity\NotificationCheck;
use App\Enums\NotificationType;
use DateTime;
use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;

class NotificationModel
{
    private int $notificationType;

    private ?int $dateAdd = null;

    private ?string $text = null;

    private ?string $categoryKey = null;

    private ?int $active = null;

    private ?bool $activated = null;

    private ?int $activatedDate = null;

    public function __construct(private string $id, NotificationType $notificationType, private ?string $actionId, private ?int $userType, private ?bool $delete)
    {
        $this->notificationType = $notificationType->value;
    }

    public function getActionId(): ?string
    {
        return $this->actionId;
    }

    public function setActionId(?Uuid $actionId): void
    {
        $this->actionId = $actionId?->__toString();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getUserType(): ?int
    {
        return $this->userType;
    }

    public function setUserType(int $userType): void
    {
        $this->userType = $userType;
    }

    public function getNotificationType(): int
    {
        return $this->notificationType;
    }

    #[OA\Property(type: 'integer', enum: [0 => 'NORMAL'])]
    public function setNotificationType(NotificationType $notificationType): void
    {
        $this->notificationType = $notificationType->value;
    }

    public function getDateAdd(): ?int
    {
        return $this->dateAdd;
    }

    public function setDateAdd(DateTime $dateAdd): void
    {
        $this->dateAdd = $dateAdd->getTimestamp() * 1000;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getDelete(): ?bool
    {
        return $this->delete;
    }

    public function setDelete(bool $delete): void
    {
        $this->delete = $delete;
    }

    public function isActive(): int
    {
        return $this->active;
    }

    public function setActive(NotificationCheck $active): void
    {
        $this->active = $active->getDateWatched()->getTimestamp() * 1000;
    }

    public function getCategoryKey(): ?string
    {
        return $this->categoryKey;
    }

    public function setCategoryKey(string $categoryKey): void
    {
        $this->categoryKey = $categoryKey;
    }

    public function getActivated(): ?bool
    {
        return $this->activated;
    }

    public function setActivated(?bool $activated): void
    {
        $this->activated = $activated;
    }

    public function getActivatedDate(): ?int
    {
        return $this->activatedDate;
    }

    public function setActivatedDate(?DateTime $activatedDate): void
    {
        $this->activatedDate = $activatedDate?->getTimestamp() * 1000;
    }
}
