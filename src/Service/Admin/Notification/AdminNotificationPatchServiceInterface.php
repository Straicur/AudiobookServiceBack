<?php

namespace App\Service\Admin\Notification;

use App\Query\Admin\AdminUserNotificationPatchQuery;
use Symfony\Component\HttpFoundation\Request;

interface AdminNotificationPatchServiceInterface
{
    public function setData(AdminUserNotificationPatchQuery $adminUserNotificationPutQuery, Request $request): self;

    public function editNotification(): void;
}
