<?php

declare(strict_types = 1);

namespace App\Query\Admin;

use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use DateTime;
use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

use function array_key_exists;

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
        max              : 8,
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

    #[Assert\Collection(
        fields: [
            'text'        => new Assert\NotBlank(allowNull: true),
            'categoryKey' => new Assert\NotBlank(allowNull: true),
            'actionId'    => new Assert\NotBlank(allowNull: true),
            'userId'      => new Assert\NotBlank(allowNull: true),
            'active'      => new Assert\NotNull(),
            'dateActive'  => new Assert\NotBlank(allowNull: true),
        ],
        allowMissingFields: true,
    )]
    protected array $additionalData = [];

    #[OA\Property(property: 'additionalData', properties: [
        new OA\Property(property: 'text', type: 'string', example: 'desc', nullable: true),
        new OA\Property(property: 'categoryKey', type: 'string', example: 'CategoryKey', nullable: true),
        new OA\Property(property: 'actionId', type: 'string', example: 'UUID', nullable: true),
        new OA\Property(property: 'active', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'dateActive', type: 'string', example: 'd.m.Y H:i', nullable: true),
    ], type    : 'object')]
    public function setAdditionalData(array $additionalData): void
    {
        if (array_key_exists('actionId', $additionalData)) {
            $additionalData['actionId'] = Uuid::fromString($additionalData['actionId']);
        }

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

    public function getNotificationType(): NotificationType
    {
        return match ($this->notificationType) {
            1 => NotificationType::NORMAL,
            2 => NotificationType::ADMIN,
            3 => NotificationType::PROPOSED,
            4 => NotificationType::NEW_CATEGORY,
            5 => NotificationType::NEW_AUDIOBOOK,
            6 => NotificationType::USER_DELETE_DECLINE,
            7 => NotificationType::USER_REPORT_ACCEPTED,
            8 => NotificationType::USER_REPORT_DENIED,
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
