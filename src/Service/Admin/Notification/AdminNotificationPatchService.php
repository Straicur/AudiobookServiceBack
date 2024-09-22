<?php

declare(strict_types=1);

namespace App\Service\Admin\Notification;

use App\Builder\NotificationBuilder;
use App\Entity\Notification;
use App\Entity\User;
use App\Enums\NotificationType;
use App\Enums\UserRolesNames;
use App\Exception\DataNotFoundException;
use App\Query\Admin\AdminUserNotificationPatchQuery;
use App\Repository\AudiobookRepository;
use App\Repository\NotificationRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Service\TranslateService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class AdminNotificationPatchService implements AdminNotificationPatchServiceInterface
{
    private AdminUserNotificationPatchQuery $adminUserNotificationPatchQuery;
    private Request $request;

    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly UserRepository $userRepository,
        private readonly RoleRepository $roleRepository,
        private readonly TranslateService $translateService,
        private readonly TagAwareCacheInterface $stockCache,
        private readonly LoggerInterface $endpointLogger,
        private readonly AudiobookRepository $audiobookRepository,
    ) {
    }

    public function setData(AdminUserNotificationPatchQuery $adminUserNotificationPutQuery, Request $request): self
    {
        $this->adminUserNotificationPatchQuery = $adminUserNotificationPutQuery;
        $this->request = $request;

        return $this;
    }

    public function editNotification(): void
    {
        $notification = $this->notificationRepository->find($this->adminUserNotificationPatchQuery->getNotificationId());

        if ($notification === null) {
            $this->endpointLogger->error('Notification dont exist');
            $this->translateService->setPreferredLanguage($this->request);
            throw new DataNotFoundException([$this->translateService->getTranslation('NotificationDontExists')]);
        }

        if ($notification->getType() !== $this->adminUserNotificationPatchQuery->getNotificationType()) {
            foreach ($notification->getUsers() as $user) {
                $notification->removeUser($user);
            }
        }

        $notificationBuilder = new NotificationBuilder($notification);

        $notificationBuilder
            ->setType($this->adminUserNotificationPatchQuery->getNotificationType())
            ->setUserAction($this->adminUserNotificationPatchQuery->getNotificationUserType());

        $additionalData = $this->adminUserNotificationPatchQuery->getAdditionalData();

        if (array_key_exists('dateActive', $additionalData)) {
            $notificationBuilder->setDateActive($additionalData['dateActive']);
        }

        if (array_key_exists('active', $additionalData)) {
            $notificationBuilder->setActive($additionalData['active']);
        } else {
            $notificationBuilder->setActive(false);
        }

        $users = [];

        if ($this->adminUserNotificationPatchQuery->getNotificationType() === NotificationType::NEW_AUDIOBOOK) {
            $users = $this->getAudiobookUsers($notification, $additionalData);
        } elseif ($this->adminUserNotificationPatchQuery->getNotificationType() === NotificationType::ADMIN) {
            $this->changeToAdminNotification($notificationBuilder, $additionalData);
        } else {
            $userRole = $this->roleRepository->findOneBy([
                'name' => UserRolesNames::USER,
            ]);

            $users = $this->userRepository->getUsersByRoleAndNoNotification($userRole, $notification);
        }

        $notificationBuilder = $this->addUsersToNotification($notificationBuilder, $users);

        if (array_key_exists('text', $additionalData)) {
            $notificationBuilder->setText($additionalData['text']);
        }

        if (array_key_exists('categoryKey', $additionalData)) {
            $notificationBuilder->setCategoryKey($additionalData['categoryKey']);
        } elseif (array_key_exists('actionId', $additionalData)) {
            $notificationBuilder->setAction($additionalData['actionId']);
        }

        $notification = $notificationBuilder->build($this->stockCache);

        $this->notificationRepository->add($notification);
    }

    public function changeToAdminNotification(NotificationBuilder $notificationBuilder, array $additionalData): NotificationBuilder
    {
        if (!array_key_exists('actionId', $additionalData)) {
            $this->endpointLogger->error('User dont exist');
            $this->translateService->setPreferredLanguage($this->request);
            throw new DataNotFoundException([$this->translateService->getTranslation('UserDontExists')]);
        }

        $user = $this->userRepository->find($additionalData['actionId']);

        if ($user === null) {
            $this->endpointLogger->error('User dont exist');
            $this->translateService->setPreferredLanguage($this->request);
            throw new DataNotFoundException([$this->translateService->getTranslation('UserDontExists')]);
        }

        $notificationBuilder->addUser($user);

        return $notificationBuilder;
    }

    /**
     * @return User[]
     */
    public function getAudiobookUsers(Notification $notification, array $additionalData): array
    {
        if (!array_key_exists('actionId', $additionalData)) {
            $this->endpointLogger->error('Audiobook dont exist');
            $this->translateService->setPreferredLanguage($this->request);
            throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
        }

        $audiobook = $this->audiobookRepository->find($additionalData['actionId']);

        if ($audiobook === null) {
            $this->endpointLogger->error('Audiobook dont exist');
            $this->translateService->setPreferredLanguage($this->request);
            throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
        }

        return $this->userRepository->getUsersWhereNoNotificationAndAudiobookInProposed($audiobook, $notification);
    }

    public function addUsersToNotification(NotificationBuilder $notificationBuilder, ?array $users): NotificationBuilder
    {
        if ($users !== null) {
            foreach ($users as $user) {
                $notificationBuilder->addUser($user);
            }
        }

        return $notificationBuilder;
    }
}
