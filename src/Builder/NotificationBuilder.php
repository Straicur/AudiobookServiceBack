<?php

namespace App\Builder;

use App\Entity\Notification;
use App\Entity\User;
use App\Enums\NotificationType;
use App\Exception\NotificationException;
use App\Model\SystemNotificationModel;
use App\Repository\UserRepository;
use Symfony\Component\Uid\Uuid;


class NotificationBuilder
{
    /**
     * @var Notification
     */
    private Notification $notification;

    /**
     * @var array
     */
    private array $metaData = [];

    public function __construct()
    {
        $this->notification = new Notification();
    }

    /**
     * @param NotificationType $notificationType
     * @return $this
     */
    public function setType(NotificationType $notificationType): NotificationBuilder
    {
        $this->notification->setType($notificationType);

        return $this;
    }

    /**
     * @param Uuid $id
     * @return $this
     */
    public function setAction(Uuid $id): NotificationBuilder
    {
        $this->notification->setActionId($id);

        return $this;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user): NotificationBuilder
    {
        $this->notification->setUser($user);

        return $this;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUserAction(User $user): NotificationBuilder
    {
        $this->metaData["userId"] = $user->getId()->__toString();

        return $this;
    }

    /**
     * @throws NotificationException
     */
    public function build(): Notification
    {
        $this->checkRequirements();

        $this->notification->setMetaData(json_encode($this->metaData));

        return $this->notification;
    }

    /**
     * @param UserRepository $userRepository
     * @param Notification $notification
     * @return SystemNotificationModel
     */
    public static function read(UserRepository $userRepository, Notification $notification,): SystemNotificationModel
    {
        $notificationModel = new SystemNotificationModel($notification->getId(), $notification->getType(), null, null, null);

        $metaData = $notification->getMetaData();

        if (array_key_exists("userId", $metaData)) {
            $user = $userRepository->findOneBy([
                "id" => $metaData["userId"]
            ]);

            if ($user != null) {
                $notificationModel->setUserName($user->getUserInformation()->getFirstname());
            }
        }

        $notificationModel->setActionId($notification->getActionId());

        $notificationModel->setDateAdd($notification->getDateAdd());

        return $notificationModel;
    }

    /**
     * @throws NotificationException
     */
    private function checkRequirements(): void
    {
        $exception = new NotificationException(Notification::class);
        $keys = [];
        $checkAction = false;

        switch ($this->notification->getType()) {
            case NotificationType::NORMAL:
                $keys = ["userId"];
                $checkAction = true;
                break;
        }

        if (!$this->checkMetadata($keys)) {
            throw $exception;
        }

        if ($checkAction && $this->notification->getActionId() == null) {
            throw $exception;
        }
    }

    /**
     * @param array $keys
     * @return bool
     */
    private function checkMetadata(array $keys): bool
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $this->metaData) || $this->metaData[$key] == null) {
                return false;
            }
        }
        return true;
    }
}