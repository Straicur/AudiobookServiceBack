<?php

namespace App\Model;

class NotificationsSuccessModel implements ModelInterface
{
    /**
     * @var SystemNotificationModel[]
     */
    private array $systemNotifications;

    private int $page;

    private int $limit;

    private int $maxPage;

    /**
     * @param SystemNotificationModel[] $systemNotifications
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
     * @return SystemNotificationModel[]
     */
    public function getSystemNotifications(): array
    {
        return $this->systemNotifications;
    }

    /**
     * @param SystemNotificationModel[] $systemNotifications
     */
    public function setSystemNotifications(array $systemNotifications): void
    {
        $this->systemNotifications = $systemNotifications;
    }

    public function addSystemNotification(SystemNotificationModel $systemNotificationModel): void
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