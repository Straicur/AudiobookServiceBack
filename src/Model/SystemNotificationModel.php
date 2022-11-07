<?php

namespace App\Model;

use App\Enums\NotificationType;
use Symfony\Component\Uid\Uuid;
use OpenApi\Attributes as OA;

class SystemNotificationModel implements ModelInterface
{
    private string $id;

    private ?string $actionId;

    private ?string $userName;

    private int $notificationType;

    private ?int $dateAdd;

    /**
     * @param string $id
     * @param NotificationType $notificationType
     * @param string|null $actionId
     * @param string|null $userName
     */
    public function __construct(string $id, NotificationType $notificationType, ?string $actionId, ?string $userName)
    {
        $this->id = $id;
        $this->notificationType = $notificationType->value;
        $this->actionId = $actionId;
        $this->userName = $userName;
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
     * @return string|null
     */
    public function getUserName(): ?string
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     */
    public function setUserName(string $userName): void
    {
        $this->userName = $userName;
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
        $this->dateAdd = $dateAdd->getTimestamp();
    }
}