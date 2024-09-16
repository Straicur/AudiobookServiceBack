<?php

declare(strict_types=1);

namespace App\Builder;

use App\Entity\Notification;
use App\Entity\NotificationCheck;
use App\Entity\User;
use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Enums\UserStockCacheTags;
use App\Exception\NotificationException;
use App\Model\Common\NotificationModel;
use DateTime;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class NotificationBuilder
{
    private Notification $notification;

    private array $metaData = [];

    public function __construct(?Notification $notification = null)
    {
        if ($notification !== null) {
            $this->notification = $notification;
        } else {
            $this->notification = new Notification();
        }
    }

    public function setType(NotificationType $notificationType): static
    {
        $this->notification->setType($notificationType);

        return $this;
    }

    public function setAction(Uuid $id): static
    {
        $this->notification->setActionId($id);

        return $this;
    }

    public function setDateActive(DateTime $dateActive): static
    {
        $this->notification->setDateActive($dateActive);

        return $this;
    }

    public function setActive(bool $active): static
    {
        $this->notification->setActive($active);

        return $this;
    }

    public function addUser(User $user): static
    {
        $this->notification->addUser($user);

        return $this;
    }

    public function setUserAction(NotificationUserType $type): static
    {
        $this->metaData['user'] = $type->value;

        return $this;
    }

    public function setText(string $text): static
    {
        $this->metaData['text'] = $text;

        return $this;
    }

    public function setCategoryKey(string $text): static
    {
        $this->metaData['categoryKey'] = $text;

        return $this;
    }

    public function build(?TagAwareCacheInterface $stockCache = null): Notification
    {
        $this->checkRequirements();

        $this->notification->setMetaData(json_encode($this->metaData));

        $stockCache?->invalidateTags([UserStockCacheTags::USER_NOTIFICATIONS->value]);

        return $this->notification;
    }

    public static function read(Notification $notification, ?NotificationCheck $notificationCheck = null): NotificationModel
    {
        $notificationModel = new NotificationModel((string)$notification->getId(), $notification->getType(), null, null, null);

        $metaData = $notification->getMetaData();

        if (array_key_exists('user', $metaData) && $metaData['user'] !== null) {
            $notificationModel->setUserType($metaData['user']);
        }

        if (array_key_exists('text', $metaData) && $metaData['text'] !== "") {
            $notificationModel->setText($metaData['text']);
        }

        if (array_key_exists('categoryKey', $metaData) && $metaData['categoryKey'] !== "") {
            $notificationModel->setCategoryKey($metaData['categoryKey']);
        }

        if ($notificationCheck !== null) {
            $notificationModel->setActive($notificationCheck);
        }

        if ($notification->getType() !== NotificationType::NEW_CATEGORY) {
            $notificationModel->setActionId($notification->getActionId());
        }

        $notificationModel->setDateAdd($notification->getDateAdd());

        $notificationModel->setDelete($notification->getDeleted());

        return $notificationModel;
    }

    private function checkRequirements(): void
    {
        $exception = new NotificationException(Notification::class);
        $keys = [];

        switch ($this->notification->getType()) {
            case NotificationType::NORMAL:
            case NotificationType::ADMIN:
            case NotificationType::PROPOSED:
            case NotificationType::NEW_AUDIOBOOK:
            case NotificationType::USER_REPORT_ACCEPTED:
            case NotificationType::USER_REPORT_DENIED:
            case NotificationType::USER_DELETE_DECLINE:
                $keys = ['user'];
                break;
            case NotificationType::NEW_CATEGORY:
                $keys = ['categoryKey', 'user'];
                break;
        }

        if (!$this->checkMetadata($keys)) {
            throw $exception;
        }
    }

    private function checkMetadata(array $keys): bool
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $this->metaData) || $this->metaData[$key] === null) {
                return false;
            }
        }
        return true;
    }
}
