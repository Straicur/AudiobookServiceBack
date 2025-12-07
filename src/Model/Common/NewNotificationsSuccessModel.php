<?php

declare(strict_types = 1);

namespace App\Model\Common;

use App\Model\ModelInterface;

class NewNotificationsSuccessModel implements ModelInterface
{
    public function __construct(private int $newNotifications) {}

    public function getNewNotifications(): int
    {
        return $this->newNotifications;
    }

    public function setNewNotifications(int $newNotifications): void
    {
        $this->newNotifications = $newNotifications;
    }
}
