<?php

namespace App\Query;

use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Exception\InvalidJsonDataException;
use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class AdminUserNotificationPutQuery
{
    #[Assert\NotNull(message: "NotificationType is null")]
    #[Assert\NotBlank(message: "NotificationType is empty")]
    #[Assert\Type(type: "integer")]
    #[Assert\Range(
        notInRangeMessage: 'You must be between {{ min }} and {{ max }}',
        min: 1,
        max: 5,
    )]
    private int $notificationType;

    #[Assert\NotNull(message: "NotificationUserType is null")]
    #[Assert\NotBlank(message: "NotificationUserType is empty")]
    #[Assert\Type(type: "integer")]
    #[Assert\Range(
        notInRangeMessage: 'You must be between {{ min }} and {{ max }}',
        min: 1,
        max: 2,
    )]
    private int $notificationUserType;

    protected array $additionalData = [];

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('additionalData', new Assert\Collection([
            'fields' => [
                'text' => new Assert\Optional([
                    new Assert\NotBlank(message: "Text is empty"),
                    new Assert\NotNull(),
                    new Assert\Type("string")
                ]),
                'actionId' => new Assert\Optional([
                    new Assert\NotBlank(message: "ActionId is empty"),
                    new Assert\NotNull(),
                    new Assert\Uuid()
                ]),
                'userId' => new Assert\Optional([
                    new Assert\NotBlank(message: "UserId is empty"),
                    new Assert\NotNull(),
                    new Assert\Uuid()
                ]),
            ]
        ]));
    }

    /**
     * @param array $additionalData
     */
    #[OA\Property(property: "additionalData", properties: [
        new OA\Property(property: "text", type: "string", example: "desc", nullable: true),
        new OA\Property(property: "actionId", type: "string", example: "UUID", nullable: true),
        new OA\Property(property: "userId", type: "string", example: "UUID", nullable: true),
    ], type: "object")]
    public function setAdditionalData(array $additionalData): void
    {
        if (array_key_exists("actionId", $additionalData)) {
            $additionalData["actionId"] = Uuid::fromString($additionalData["actionId"]);
        }

        if (array_key_exists("userId", $additionalData)) {
            $additionalData["userId"] = Uuid::fromString($additionalData["userId"]);
        }

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
     * @return NotificationType
     */
    public function getNotificationType(): NotificationType
    {
        return match ($this->notificationType) {
            1 => NotificationType::NORMAL,
            2 => NotificationType::ADMIN,
            4 => NotificationType::NEW_CATEGORY,
            5 => NotificationType::NEW_AUDIOBOOK,
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
            2 => NotificationUserType::SYSTEM
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