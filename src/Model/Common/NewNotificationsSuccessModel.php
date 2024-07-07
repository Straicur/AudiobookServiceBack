<?php

declare(strict_types=1);

namespace App\Model\Common;

use App\Model\ModelInterface;

class NewNotificationsSuccessModel implements ModelInterface
{
    private int $newNotifications;

    public function __construct(int $newNotifications)
    {
        $this->newNotifications = $newNotifications;
    }

    public function getNewNotifications(): int
    {
        return $this->newNotifications;
    }

    public function setNewNotifications(int $newNotifications): void
    {
        $this->newNotifications = $newNotifications;
    }
}
