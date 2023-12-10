<?php

namespace App\Model\Common;

use App\Entity\NotificationCheck;
use App\Enums\NotificationType;
use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;

class NotificationModel
{
    private string $id;

    private ?string $actionId;

    private ?int $userType;

    private int $notificationType;

    private ?int $dateAdd;

    private ?string $text;

    private ?string $categoryKey;
    private ?bool $delete;

    private ?int $active;

    /**
     * @param string $id
     * @param NotificationType $notificationType
     * @param string|null $actionId
     * @param int|null $userType
     * @param bool|null $delete
     */
    public function __construct(string $id, NotificationType $notificationType, ?string $actionId, ?int $userType, ?bool $delete)
    {
        $this->id = $id;
        $this->notificationType = $notificationType->value;
        $this->actionId = $actionId;
        $this->userType = $userType;
        $this->delete = $delete;
    }

    /**
     * @return string|null
     */
    public function getActionId(): ?string
    {
        return $this->actionId;
    }

    /**
     * @param Uuid|null $actionId
     */
    public function setActionId(?Uuid $actionId): void
    {
        $this->actionId = $actionId?->__toString();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getUserType(): ?int
    {
        return $this->userType;
    }

    /**
     * @param int $userType
     */
    public function setUserType(int $userType): void
    {
        $this->userType = $userType;
    }

    /**
     * @return int
     */
    public function getNotificationType(): int
    {
        return $this->notificationType;
    }

    /**
     * @param NotificationType $notificationType
     */
    #[OA\Property(type: "integer", enum: [0 => 'NORMAL'])]
    public function setNotificationType(NotificationType $notificationType): void
    {
        $this->notificationType = $notificationType->value;
    }

    /**
     * @return int|null
     */
    public function getDateAdd(): ?int
    {
        return $this->dateAdd;
    }

    /**
     * @param \DateTime|null $dateAdd
     */
    public function setDateAdd(?\DateTime $dateAdd): void
    {
        $this->dateAdd = $dateAdd->getTimestamp() * 1000;
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @param string|null $text
     */
    public function setText(?string $text): void
    {
        $this->text = $text;
    }

    /**
     * @return bool|null
     */
    public function getDelete(): ?bool
    {
        return $this->delete;
    }

    /**
     * @param bool|null $delete
     */
    public function setDelete(?bool $delete): void
    {
        $this->delete = $delete;
    }

    /**
     * @return int
     */
    public function isActive(): int
    {
        return $this->active;
    }

    /**
     * @param NotificationCheck $active
     */
    public function setActive(NotificationCheck $active): void
    {
        $this->active = $active->getDateWatched()->getTimestamp();
    }

    /**
     * @return string|null
     */
    public function getCategoryKey(): ?string
    {
        return $this->categoryKey;
    }

    /**
     * @param string|null $categoryKey
     */
    public function setCategoryKey(?string $categoryKey): void
    {
        $this->categoryKey = $categoryKey;
    }

}