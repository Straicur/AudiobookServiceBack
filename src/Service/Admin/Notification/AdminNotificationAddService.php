<?php

declare(strict_types=1);

namespace App\Service\Admin\Notification;

use App\Builder\NotificationBuilder;
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
use App\Service\TranslateService;
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
        private readonly TranslateService $translateService,
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
        //TODO tu dodatkowo dlaczego jest już dodawane ?
        $notificationBuilder = match ($this->adminUserNotificationPutQuery->getNotificationType()) {
            NotificationType::NORMAL => $this->addNormalNotification($notificationBuilder, $additionalData),
            NotificationType::ADMIN => $this->addAdminNotification($notificationBuilder, $additionalData),
            NotificationType::NEW_CATEGORY => $this->addNewCategoryNotification($notificationBuilder, $additionalData),
            NotificationType::NEW_AUDIOBOOK => $this->addNewAudiobookNotification($notificationBuilder, $additionalData),
            default => throw new DataNotFoundException('')
        };

        if (array_key_exists('dateActive', $additionalData)) {
            $notificationBuilder->setDateActive($additionalData['dateActive']);
        }

        if (array_key_exists('active', $additionalData)) {
            $notificationBuilder->setActive($additionalData['active']);
        } else {
            $notificationBuilder->setActive(false);
        }

        $notification = $notificationBuilder->build($this->stockCache);

        $this->notificationRepository->add($notification);
    }

    private function addNormalNotification(NotificationBuilder $notificationBuilder, array $additionalData): NotificationBuilder
    {
        $userRole = $this->roleRepository->findOneBy([
            'name' => UserRolesNames::USER->value,
        ]);

        $users = $this->userRepository->getUsersByRole($userRole);

        $notificationBuilder
            ->setType($this->adminUserNotificationPutQuery->getNotificationType())
            ->setUserAction($this->adminUserNotificationPutQuery->getNotificationUserType());

        if (array_key_exists('text', $additionalData)) {
            $notificationBuilder->setText($additionalData['text']);
        }

        foreach ($users as $user) {
            $notificationBuilder->addUser($user);
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

        $userRole = $this->roleRepository->findOneBy([
            'name' => UserRolesNames::USER,
        ]);

        $users = $this->userRepository->getUsersByRole($userRole);

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

        foreach ($users as $user) {
            $notificationBuilder->addUser($user);
        }

        return $notificationBuilder;
    }

    private function addNewAudiobookNotification(NotificationBuilder $notificationBuilder, array $additionalData): NotificationBuilder
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

        $users = $this->userRepository->getUsersWhereAudiobookInProposed($audiobook);

        $notificationBuilder
            ->setType($this->adminUserNotificationPutQuery->getNotificationType())
            ->setUserAction($this->adminUserNotificationPutQuery->getNotificationUserType())
            ->setAction($additionalData['actionId']);

        if (array_key_exists('text', $additionalData)) {
            $notificationBuilder->setText($additionalData['text']);
        }

        foreach ($users as $user) {
            $notificationBuilder->addUser($user);
        }

        return $notificationBuilder;
    }
}
