<?php

declare(strict_types=1);

namespace App\Service\Admin\Notification;

use App\Builder\NotificationBuilder;
use App\Enums\UserRolesNames;
use App\Exception\DataNotFoundException;
use App\Query\Admin\AdminUserNotificationPatchQuery;
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

        $notificationBuilder = new NotificationBuilder($notification);

        $notificationBuilder
            ->setType($this->adminUserNotificationPatchQuery->getNotificationType())
            ->setUserAction($this->adminUserNotificationPatchQuery->getNotificationUserType());

        $additionalData = $this->adminUserNotificationPatchQuery->getAdditionalData();

        //TODO tu brakuje odpowiedniego dodawania userów i usuwania starych przy zmianie typu który jest inny od starego


        if (array_key_exists('dateActive', $additionalData)) {
            $notificationBuilder->setDateActive($additionalData['dateActive']);
        }

        if (array_key_exists('active', $additionalData)) {
            $notificationBuilder->setActive($additionalData['active']);
        } else {
            $notificationBuilder->setActive(false);
        }

        //TODO tu nie powinny być teraz znowu te same osoby tylko te do których nie ma i oddzielnie dla (NORMAL, NEW_CATEGORY) i NEW_AUDIOBOOK
        $userRole = $this->roleRepository->findOneBy([
            'name' => UserRolesNames::USER,
        ]);

        $users = $this->userRepository->getUsersByRoleAndNoNotification($userRole, $notification);

        foreach ($users as $user) {
            $notificationBuilder->addUser($user);
        }

        if (array_key_exists('text', $additionalData)) {
            $notificationBuilder->setText($additionalData['text']);
        }
        if (array_key_exists('categoryKey', $additionalData)) {
            $notificationBuilder->setCategoryKey($additionalData['categoryKey']);
        } else {
            $notificationBuilder->setAction($this->adminUserNotificationPatchQuery->getActionId());
        }

        $notification = $notificationBuilder->build($this->stockCache);

        $this->notificationRepository->add($notification);
    }
}
