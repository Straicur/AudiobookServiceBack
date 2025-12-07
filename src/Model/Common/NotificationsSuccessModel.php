<?php

declare(strict_types = 1);

namespace App\Model\Common;

use App\Model\ModelInterface;

class NotificationsSuccessModel implements ModelInterface
{
    /**
     * @param NotificationModel[] $systemNotifications
     */
    public function __construct(private array $systemNotifications, private int $page, private int $limit, private int $maxPage) {}

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
