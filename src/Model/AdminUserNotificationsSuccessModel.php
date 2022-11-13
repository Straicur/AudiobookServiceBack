<?php

namespace App\Model;

class AdminUserNotificationsSuccessModel implements ModelInterface
{
    /**
     * @var NotificationModel[]
     */
    private array $systemNotifications;

    private int $page;

    private int $limit;

    private int $maxPage;

    /**
     * @param NotificationModel[] $systemNotifications
     * @param int $page
     * @param int $limit
     * @param int $maxPage
     */
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

    /**
     * @return int
     */
    public function getMaxPage(): int
    {
        return $this->maxPage;
    }

    /**
     * @param int $maxPage
     */
    public function setMaxPage(int $maxPage): void
    {
        $this->maxPage = $maxPage;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }
}