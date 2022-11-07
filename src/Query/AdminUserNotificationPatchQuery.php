<?php

namespace App\Query;

use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class AdminUserNotificationPatchQuery
{
    #[Assert\NotNull(message: "NotificationType is null")]
    #[Assert\NotBlank(message: "NotificationType is empty")]
    #[Assert\Type(type: "integer")]
    private int $notificationType;

    #[Assert\NotNull(message: "NotificationUserType is null")]
    #[Assert\NotBlank(message: "NotificationUserType is empty")]
    #[Assert\Type(type: "integer")]
    private int $notificationUserType;

    #[Assert\NotNull(message: "ActionId is null")]
    #[Assert\NotBlank(message: "ActionId is blank")]
    #[Assert\Uuid]
    private Uuid $actionId;

    #[Assert\NotNull(message: "UserId is null")]
    #[Assert\NotBlank(message: "UserId is blank")]
    #[Assert\Uuid]
    private Uuid $userId;


    protected array $additionalData = [];

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('additionalData', new Assert\Collection([
            'fields' => [
                'text' => new Assert\Optional([
                    new Assert\NotBlank(message: "Text is empty"),
                    new Assert\NotNull(),
                    new Assert\Uuid()
                ])
            ]
        ]));
    }

    /**
     * @param array $additionalData
     */
    #[OA\Property(property: "additionalData", properties: [
        new OA\Property(property: "text", type: "string", example: "desc", nullable: true),
    ], type: "object")]
    public function setAdditionalData(array $additionalData): void
    {
        $this->additionalData = $additionalData;
    }

    /**
     * @return string[]
     */
    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    /**
     * @return Uuid
     */
    #[OA\Property(type: "string", example: "60266c4e-16e6-1ecc-9890-a7e8b0073d3b")]
    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     */
    public function setUserId(string $userId): void
    {
        $this->userId = Uuid::fromString($userId);;
    }

    /**
     * @return Uuid
     */
    #[OA\Property(type: "string", example: "60266c4e-16e6-1ecc-9890-a7e8b0073d3b")]
    public function getActionId(): Uuid
    {
        return $this->actionId;
    }

    /**
     * @param string $actionId
     */
    public function setActionId(string $actionId): void
    {
        $this->actionId = Uuid::fromString($actionId);;
    }

    /**
     * @return NotificationType
     */
    public function getNotificationType(): NotificationType
    {
        return match ($this->notificationType) {
            1 => NotificationType::NORMAL,
            2 => NotificationType::ADMIN,
            3 => NotificationType::PROPOSED,
            4 => NotificationType::NEW_CATEGORY,
            5 => NotificationType::NEW_AUDIOBOOK,
            6 => NotificationType::USER_DELETE_DECLINE,
        };
    }

    /**
     * @param int $notificationType
     */
    public function setNotificationType(int $notificationType): void
    {
        $this->notificationType = $notificationType;
    }

    /**
     * @return NotificationUserType
     */
    public function getNotificationUserType(): NotificationUserType
    {
        return match ($this->notificationUserType) {
            1 => NotificationUserType::ADMIN,
            2 => NotificationUserType::SYSTEM,
        };
    }

    /**
     * @param int $notificationUserType
     */
    public function setNotificationUserType(int $notificationUserType): void
    {
        $this->notificationUserType = $notificationUserType;
    }

}