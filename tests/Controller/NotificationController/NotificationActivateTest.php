<?php

declare(strict_types=1);

namespace App\Tests\Controller\NotificationController;

use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Repository\NotificationCheckRepository;
use App\Tests\AbstractWebTest;

class NotificationActivateTest extends AbstractWebTest
{
    public function testNotificationActivateCorrect(): void
    {
        $notificationCheckRepository = $this->getService(NotificationCheckRepository::class);

        $this->assertInstanceOf(NotificationCheckRepository::class, $notificationCheckRepository);

        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $notification1 = $this->databaseMockManager->testFunc_addNotifications([$user1, $user2], NotificationType::ADMIN, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);
        $this->databaseMockManager->testFunc_addNotifications([$user1,
            $user2], NotificationType::PROPOSED, $user1->getProposedAudiobooks()->getId(), NotificationUserType::ADMIN);
        $this->databaseMockManager->testFunc_addNotifications([$user1,
            $user2], NotificationType::USER_DELETE_DECLINE, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);

        $this->databaseMockManager->testFunc_addNotificationCheck($user1, $notification1);

        $content = [
            'notificationId' => $notification1->getId()
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $this->webClient->request('PUT', '/api/notification/activate', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $this->assertCount(1, $notificationCheckRepository->findAll());
    }

    public function testNotificationActivateCorrectAdd(): void
    {
        $notificationCheckRepository = $this->getService(NotificationCheckRepository::class);

        $this->assertInstanceOf(NotificationCheckRepository::class, $notificationCheckRepository);

        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $notification1 = $this->databaseMockManager->testFunc_addNotifications([$user1, $user2], NotificationType::ADMIN, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);
        $this->databaseMockManager->testFunc_addNotifications([$user1,
            $user2], NotificationType::PROPOSED, $user1->getProposedAudiobooks()->getId(), NotificationUserType::ADMIN);
        $this->databaseMockManager->testFunc_addNotifications([$user1,
            $user2], NotificationType::USER_DELETE_DECLINE, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);

        $this->databaseMockManager->testFunc_addNotificationCheck($user1, $notification1);

        $content = [
            'notificationId' => $notification1->getId()
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $this->webClient->request('PUT', '/api/notification/activate', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);
        $this->assertNotNull($notificationCheckRepository->findOneBy([
            'user' => $user1->getId(),
            'notification' => $notification1->getId()
        ]));
    }

    public function testNotificationActivateEmptyRequestData(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', notActive: true);
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $this->webClient->request('PUT', '/api/notification/activate', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testNotificationActivatePermission(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx', notActive: true);
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $notification1 = $this->databaseMockManager->testFunc_addNotifications([$user1, $user2], NotificationType::ADMIN, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);

        $content = [
            'notificationId' => $notification1->getId()
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $this->webClient->request('PUT', '/api/notification/activate', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testNotificationActivateLogOut(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', notActive: true);
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $notification1 = $this->databaseMockManager->testFunc_addNotifications([$user1, $user2], NotificationType::ADMIN, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);

        $content = [
            'notificationId' => $notification1->getId()
        ];

        $this->webClient->request('PUT', '/api/notification/activate', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
