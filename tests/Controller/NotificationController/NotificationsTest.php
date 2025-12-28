<?php

declare(strict_types=1);

namespace App\Tests\Controller\NotificationController;

use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Exception\NotificationException;
use App\Tests\AbstractWebTest;

class NotificationsTest extends AbstractWebTest
{
    public function testNotificationsCorrect(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $notification1 = $this->databaseMockManager->testFunc_addNotifications([$user1, $user2], NotificationType::ADMIN, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);
        $this->databaseMockManager->testFunc_addNotifications([$user1,
            $user2], NotificationType::PROPOSED, $user1->getProposedAudiobooks()->getId(), NotificationUserType::ADMIN);
        $this->databaseMockManager->testFunc_addNotifications([$user1,
            $user2], NotificationType::USER_DELETE_DECLINE, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);

        $this->databaseMockManager->testFunc_addNotificationCheck($user1, $notification1);

        $content = [
            'page' => 0,
            'limit' => 10,
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $this->webClient->request('POST', '/api/notifications', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = $this->webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('systemNotifications', $responseContent);
        $this->assertArrayHasKey('page', $responseContent);
        $this->assertArrayHasKey('limit', $responseContent);
        $this->assertArrayHasKey('maxPage', $responseContent);
        $this->assertCount(3, $responseContent['systemNotifications']);
        $this->assertNotNull($responseContent['systemNotifications'][0]['active']);
    }

    public function testNotificationsEmptyRequestData(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', notActive: true);
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $this->webClient->request('POST', '/api/notifications', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testNotificationsPermission(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx', notActive: true);
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');

        $content = [
            'page' => 0,
            'limit' => 10,
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $this->webClient->request('POST', '/api/notifications', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testNotificationsLogOut(): void
    {
        $content = [
            'page' => 0,
            'limit' => 10,
        ];

        $this->webClient->request('POST', '/api/notifications', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
