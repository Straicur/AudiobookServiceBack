<?php

declare(strict_types=1);

namespace App\Model\Admin;

use App\Model\Common\NotificationModel;
use App\Model\ModelInterface;

class AdminUserNotificationsSuccessModel implements ModelInterface
{
    /**
     * @var NotificationModel[]
     */
    private array $systemNotifications;

    private int $page;

    private int $limit;

    private int $maxPage;

    public function __construct(array $systemNotifications, int $page, int $limit, int $maxPage)
    {
        $this->systemNotifications = $systemNotifications;
        $this->page = $page;
        $this->limit = $limit;
        $this->maxPage = $maxPage;
    }

    /**
     * @return NotificationModel[]
     */
    public function getSystemNotifications(): array
    {
        return $this->systemNotifications;
    }

    /**
     * @param NotificationModel[] $systemNotifications
     */
    public function setSystemNotifications(array $systemNotifications): void
    {
        $this->systemNotifications = $systemNotifications;
    }

    public function addSystemNotification(NotificationModel $systemNotificationModel): void
    {
        $this->systemNotifications[] = $systemNotificationModel;
    }
    
    public function getMaxPage(): int
    {
        return $this->maxPage;
    }

    public function setMaxPage(int $maxPage): void
    {
        $this->maxPage = $maxPage;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
    
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }
}