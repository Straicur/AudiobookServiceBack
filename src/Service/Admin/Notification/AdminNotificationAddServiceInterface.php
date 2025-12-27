<?php

declare(strict_types = 1);

namespace App\Service\Admin\Notification;

use App\Query\Admin\AdminUserNotificationPutQuery;
use Symfony\Component\HttpFoundation\Request;

interface AdminNotificationAddServiceInterface
{
    public function setData(AdminUserNotificationPutQuery $adminUserNotificationPutQuery, Request $request): self;

    public function addNotification(): void;
}
