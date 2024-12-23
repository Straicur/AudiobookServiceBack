<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminUserNotificationsController;

use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Tests\AbstractWebTest;

class AdminUserNotificationsTest extends AbstractWebTest
{
    public function testAdminUserNotificationsCorrect(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123128', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user3 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest', 'User'], true, 'zaq12wsx');
        $user4 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test4@cos.pl', '+48123123126', ['Guest', 'User'], true, 'zaq12wsx');
        $user5 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test5@cos.pl', '+48123123125', ['Guest', 'User'], true, 'zaq12wsx');

        $this->databaseMockManager->testFunc_addNotifications([$user1,$user2,$user4], NotificationType::ADMIN, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);
        $this->databaseMockManager->testFunc_addNotifications([$user1,$user3,$user5], NotificationType::PROPOSED, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);
        $this->databaseMockManager->testFunc_addNotifications([$user1,$user2], NotificationType::USER_DELETE_DECLINE, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);

        $content = [
            'page' => 0,
            'limit' => 10,
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        self::$webClient->request('POST', '/api/admin/user/notifications', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('systemNotifications', $responseContent);
        $this->assertArrayHasKey('page', $responseContent);
        $this->assertArrayHasKey('limit', $responseContent);
        $this->assertArrayHasKey('maxPage', $responseContent);
        $this->assertCount(3, $responseContent['systemNotifications']);
    }

    public function testAdminUserNotificationsSpecificSearchCorrect(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123125', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user3 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123126', ['Guest', 'User'], true, 'zaq12wsx');
        $user4 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test4@cos.pl', '+48123123127', ['Guest', 'User'], true, 'zaq12wsx');
        $user5 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test5@cos.pl', '+48123123128', ['Guest', 'User'], true, 'zaq12wsx');

        $this->databaseMockManager->testFunc_addNotifications([$user1, $user2, $user4], NotificationType::NORMAL, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM, 't1');
        $this->databaseMockManager->testFunc_addNotifications([$user1, $user3, $user5], NotificationType::PROPOSED, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM, 't2');
        $this->databaseMockManager->testFunc_addNotifications([$user1, $user2], NotificationType::USER_DELETE_DECLINE, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM, 't3', true);
        $this->databaseMockManager->testFunc_addNotifications([$user1, $user2], NotificationType::NORMAL, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM, 't4', true);

        $content = [
            'page' => 0,
            'limit' => 10,
            'searchData' => [
                'text' => 't',
                'type' => 1,
                'deleted' => false,
                'order' => 1
            ]
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        self::$webClient->request('POST', '/api/admin/user/notifications', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('systemNotifications', $responseContent);
        $this->assertArrayHasKey('page', $responseContent);
        $this->assertArrayHasKey('limit', $responseContent);
        $this->assertArrayHasKey('maxPage', $responseContent);
        $this->assertCount(1, $responseContent['systemNotifications']);
    }

    public function testAdminUserNotificationsEmptyRequestData(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', notActive: true);
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123125', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123126', ['Guest', 'User'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test4@cos.pl', '+48123123127', ['Guest', 'User'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test5@cos.pl', '+48123123128', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        self::$webClient->request('POST', '/api/admin/user/notifications', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function testAdminUserNotificationsPermission(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User',], true, 'zaq12wsx', notActive: true);
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123125', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123126', ['Guest', 'User'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test4@cos.pl', '+48123123127', ['Guest', 'User'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test5@cos.pl', '+48123123128', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [
            'page' => 0,
            'limit' => 10,
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        self::$webClient->request('POST', '/api/admin/user/notifications', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function testAdminUserNotificationsLogOut(): void
    {
        $content = [
            'page' => 0,
            'limit' => 10,
        ];

        self::$webClient->request('POST', '/api/admin/user/notifications', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
