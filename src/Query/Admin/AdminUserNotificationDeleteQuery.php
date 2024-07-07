<?php

namespace App\Query\Admin;

use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class AdminUserNotificationDeleteQuery
{
    #[Assert\NotNull(message: 'NotificationId is null')]
    #[Assert\NotBlank(message: 'NotificationId is blank')]
    #[Assert\Uuid]
    private Uuid $notificationId;

    #[Assert\NotNull(message: 'Delete is null')]
    #[Assert\Type(type: 'boolean')]
    private bool $delete;

    /**
     * @return Uuid
     */
    #[OA\Property(type: 'string', example: '60266c4e-16e6-1ecc-9890-a7e8b0073d3b')]
    public function getNotificationId(): Uuid
    {
        return $this->notificationId;
    }

    /**
     * @param string $notificationId
     */
    public function setNotificationId(string $notificationId): void
    {
        $this->notificationId = Uuid::fromString($notificationId);
    }

    /**
     * @return bool
     */
    public function isDelete(): bool
    {
        return $this->delete;
    }

    /**
     * @param bool $delete
     */
    public function setDelete(bool $delete): void
    {
        $this->delete = $delete;
    }
}
