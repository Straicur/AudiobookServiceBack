<?php

declare(strict_types=1);

namespace App\Service\Admin\Notification;

use App\Builder\NotificationBuilder;
use App\Entity\Audiobook;
use App\Entity\Notification;
use App\Enums\NotificationType;
use App\Enums\UserRolesNames;
use App\Exception\DataNotFoundException;
use App\Exception\InvalidJsonDataException;
use App\Query\Admin\AdminUserNotificationPutQuery;
use App\Repository\AudiobookCategoryRepository;
use App\Repository\AudiobookRepository;
use App\Repository\NotificationRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Service\TranslateServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class AdminNotificationAddService implements AdminNotificationAddServiceInterface
{
    private AdminUserNotificationPutQuery $adminUserNotificationPutQuery;
    private Request $request;

    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly UserRepository $userRepository,
        private readonly RoleRepository $roleRepository,
        private readonly AudiobookRepository $audiobookRepository,
        private readonly AudiobookCategoryRepository $categoryRepository,
        private readonly TranslateServiceInterface $translateService,
        private readonly TagAwareCacheInterface $stockCache,
        private readonly LoggerInterface $endpointLogger,
    ) {
    }

    public function setData(AdminUserNotificationPutQuery $adminUserNotificationPutQuery, Request $request): self
    {
        $this->adminUserNotificationPutQuery = $adminUserNotificationPutQuery;
        $this->request = $request;

        return $this;
    }

    public function addNotification(): void
    {
        $additionalData = $this->adminUserNotificationPutQuery->getAdditionalData();

        $notificationBuilder = new NotificationBuilder();

        if (array_key_exists('dateActive', $additionalData)) {
            $notificationBuilder->setDateActive($additionalData['dateActive']);
        }

        if (array_key_exists('active', $additionalData)) {
            $notificationBuilder->setActive($additionalData['active']);
        } else {
            $notificationBuilder->setActive(false);
        }

        $audiobook = null;

        switch ($this->adminUserNotificationPutQuery->getNotificationType()) {
            case NotificationType::NORMAL:
                $notificationBuilder = $this->addNormalNotification($notificationBuilder, $additionalData);
                break;
            case NotificationType::ADMIN:
                $notificationBuilder = $this->addAdminNotification($notificationBuilder, $additionalData);
                break;
            case NotificationType::NEW_CATEGORY:
                $notificationBuilder = $this->addNewCategoryNotification($notificationBuilder, $additionalData);
                break;
            case NotificationType::PROPOSED:
                $notificationBuilder = $this->addNewCategoryNotification($notificationBuilder, $additionalData);
                break;
            case NotificationType::NEW_AUDIOBOOK:
                [$notificationBuilder, $audiobook] = $this->addNewAudiobookNotification($notificationBuilder, $additionalData);
                break;
            default:
                throw new DataNotFoundException([$this->translateService->getTranslation('NotificationTypeNotAllowed')]);
        }

        $notification = $notificationBuilder->build($this->stockCache);

        $this->notificationRepository->add($notification);

        if ($this->adminUserNotificationPutQuery->getNotificationType() === NotificationType::ADMIN) {
            return;
        }

        $notification = match ($this->adminUserNotificationPutQuery->getNotificationType()) {
            NotificationType::NORMAL, NotificationType::NEW_CATEGORY => $this->sendToUsers($notification),
            NotificationType::NEW_AUDIOBOOK => $this->sendToProposalUsers($notification, $audiobook)
        };

        $this->notificationRepository->add($notification);
    }

    private function addNormalNotification(NotificationBuilder $notificationBuilder, array $additionalData): NotificationBuilder
    {
        $notificationBuilder
            ->setType($this->adminUserNotificationPutQuery->getNotificationType())
            ->setUserAction($this->adminUserNotificationPutQuery->getNotificationUserType());

        if (array_key_exists('text', $additionalData)) {
            $notificationBuilder->setText($additionalData['text']);
        }

        return $notificationBuilder;
    }

    private function addAdminNotification(NotificationBuilder $notificationBuilder, array $additionalData): NotificationBuilder
    {
        if (!array_key_exists('userId', $additionalData)) {
            $this->endpointLogger->error('Invalid given Query no userId');
            $this->translateService->setPreferredLanguage($this->request);
            throw new InvalidJsonDataException($this->translateService);
        }

        $user = $this->userRepository->find($additionalData['userId']);

        if ($user === null) {
            $this->endpointLogger->error('User dont exist');
            $this->translateService->setPreferredLanguage($this->request);
            throw new DataNotFoundException([$this->translateService->getTranslation('UserDontExists')]);
        }

        $notificationBuilder
            ->setType($this->adminUserNotificationPutQuery->getNotificationType())
            ->setUserAction($this->adminUserNotificationPutQuery->getNotificationUserType())
            ->addUser($user)
            ->setAction($user->getId());

        if (array_key_exists('text', $additionalData)) {
            $notificationBuilder->setText($additionalData['text']);
        }

        return $notificationBuilder;
    }

    private function addNewCategoryNotification(NotificationBuilder $notificationBuilder, array $additionalData): NotificationBuilder
    {
        if (!array_key_exists('categoryKey', $additionalData)) {
            $this->endpointLogger->error('Invalid given Query no categoryKey');
            $this->translateService->setPreferredLanguage($this->request);
            throw new InvalidJsonDataException($this->translateService);
        }

        $category = $this->categoryRepository->findOneBy([
            'categoryKey' => $additionalData['categoryKey'],
        ]);

        if ($category === null) {
            $this->endpointLogger->error('Category dont exist');
            $this->translateService->setPreferredLanguage($this->request);
            throw new DataNotFoundException([$this->translateService->getTranslation('CategoryDontExists')]);
        }

        $notificationBuilder
            ->setType($this->adminUserNotificationPutQuery->getNotificationType())
            ->setUserAction($this->adminUserNotificationPutQuery->getNotificationUserType())
            ->setAction($category->getId())
            ->setCategoryKey($category->getCategoryKey());

        if (array_key_exists('text', $additionalData)) {
            $notificationBuilder->setText($additionalData['text']);
        }

        return $notificationBuilder;
    }

    /**
     * @return  array{NotificationBuilder, Audiobook}
     */
    private function addNewAudiobookNotification(NotificationBuilder $notificationBuilder, array $additionalData): array
    {
        if (!array_key_exists('actionId', $additionalData)) {
            $this->endpointLogger->error('Invalid given Query no actionId');
            $this->translateService->setPreferredLanguage($this->request);
            throw new InvalidJsonDataException($this->translateService);
        }

        $audiobook = $this->audiobookRepository->find($additionalData['actionId']);

        if ($audiobook === null) {
            $this->endpointLogger->error('Audiobook dont exist');
            $this->translateService->setPreferredLanguage($this->request);
            throw new DataNotFoundException([$this->translateService->getTranslation('AudiobookDontExists')]);
        }

        $notificationBuilder
            ->setType($this->adminUserNotificationPutQuery->getNotificationType())
            ->setUserAction($this->adminUserNotificationPutQuery->getNotificationUserType())
            ->setAction($additionalData['actionId']);

        if (array_key_exists('text', $additionalData)) {
            $notificationBuilder->setText($additionalData['text']);
        }

        return [$notificationBuilder, $audiobook];
    }

    private function sendToUsers(Notification $notification): Notification
    {
        $userRole = $this->roleRepository->findOneBy([
            'name' => UserRolesNames::USER,
        ]);

        $users = $this->userRepository->getUsersByRole($userRole);

        foreach ($users as $user) {
            $notification->addUser($user);
        }

        return $notification;
    }

    private function sendToProposalUsers(Notification $notification, Audiobook $audiobook): Notification
    {
        $users = $this->userRepository->getUsersWhereAudiobookInProposed($audiobook);

        foreach ($users as $user) {
            $notification->addUser($user);
        }

        return $notification;
    }
}
