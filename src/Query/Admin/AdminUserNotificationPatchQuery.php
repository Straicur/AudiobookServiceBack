<?php

namespace App\Query\Admin;

use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use DateTime;
use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class AdminUserNotificationPatchQuery
{
    #[Assert\NotNull(message: 'NotificationId is null')]
    #[Assert\NotBlank(message: 'NotificationId is blank')]
    #[Assert\Uuid]
    private Uuid $notificationId;

    #[Assert\NotNull(message: 'NotificationType is null')]
    #[Assert\NotBlank(message: 'NotificationType is empty')]
    #[Assert\Type(type: 'integer')]
    #[Assert\Range(
        notInRangeMessage: 'You must be between {{ min }} and {{ max }}',
        min              : 1,
        max              : 5,
    )]
    private int $notificationType;

    #[Assert\NotNull(message: 'NotificationUserType is null')]
    #[Assert\NotBlank(message: 'NotificationUserType is empty')]
    #[Assert\Type(type: 'integer')]
    #[Assert\Range(
        notInRangeMessage: 'You must be between {{ min }} and {{ max }}',
        min              : 1,
        max              : 2,
    )]
    private int $notificationUserType;

    #[Assert\NotNull(message: 'ActionId is null')]
    #[Assert\NotBlank(message: 'ActionId is blank')]
    #[Assert\Uuid]
    private Uuid $actionId;

    protected array $additionalData = [];

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('additionalData', new Assert\Collection([
            'fields' => [
                'text'        => new Assert\Optional([
                    new Assert\NotBlank(message: 'Text is empty'),
                    new Assert\NotNull(),
                    new Assert\Type(type: 'string'),
                ]),
                'categoryKey' => new Assert\Optional([
                    new Assert\NotBlank(message: 'CategoryKey is empty'),
                    new Assert\NotNull(),
                    new Assert\Type('string'),
                ]),
                'active'     => new Assert\Optional([
                    new Assert\NotNull(message: 'Active is empty'),
                    new Assert\Type('boolean'),
                ]),
                'dateActive' => new Assert\Optional([
                    new Assert\NotBlank(message: 'DateActive is empty'),
                    new Assert\NotNull(),
                    new Assert\Type('datetime'),
                ])
            ],
        ]));
    }

    #[OA\Property(property: 'additionalData', properties: [
        new OA\Property(property: 'text', type: 'string', example: 'desc', nullable: true),
        new OA\Property(property: 'categoryKey', type: 'string', example: 'CategoryKey', nullable: true),
    ], type    : 'object')]
    public function setAdditionalData(array $additionalData): void
    {
        if (array_key_exists('dateActive', $additionalData)) {
            $additionalData['dateActive'] = DateTime::createFromFormat('d.m.Y H:i', $additionalData['dateActive']);
        }

        $this->additionalData = $additionalData;
    }

    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    #[OA\Property(type: 'string', example: '60266c4e-16e6-1ecc-9890-a7e8b0073d3b')]
    public function getNotificationId(): Uuid
    {
        return $this->notificationId;
    }

    public function setNotificationId(string $notificationId): void
    {
        $this->notificationId = Uuid::fromString($notificationId);
    }

    #[OA\Property(type: 'string', example: '60266c4e-16e6-1ecc-9890-a7e8b0073d3b')]
    public function getActionId(): Uuid
    {
        return $this->actionId;
    }

    public function setActionId(string $actionId): void
    {
        $this->actionId = Uuid::fromString($actionId);
    }

    public function getNotificationType(): NotificationType
    {
        return match ($this->notificationType) {
            1 => NotificationType::NORMAL,
            2 => NotificationType::ADMIN,
            3 => NotificationType::PROPOSED,
            4 => NotificationType::NEW_CATEGORY,
            5 => NotificationType::NEW_AUDIOBOOK,
        };
    }

    public function setNotificationType(int $notificationType): void
    {
        $this->notificationType = $notificationType;
    }

    public function getNotificationUserType(): NotificationUserType
    {
        return match ($this->notificationUserType) {
            1 => NotificationUserType::ADMIN,
            2 => NotificationUserType::SYSTEM,
        };
    }

    public function setNotificationUserType(int $notificationUserType): void
    {
        $this->notificationUserType = $notificationUserType;
    }
}
