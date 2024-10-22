<?php

declare(strict_types=1);

namespace App\Tests\Controller\NotificationController;

use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Exception\NotificationException;
use App\Tests\AbstractWebTest;

/**
 * NotificationsTest
 */
class NotificationsTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response has returned correct data
     * @return void
     * @throws NotificationException
     */
    public function test_notificationsCorrect(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $notification1 = $this->databaseMockManager->testFunc_addNotifications([$user1, $user2], NotificationType::ADMIN, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);
        $this->databaseMockManager->testFunc_addNotifications([$user1,
            $user2], NotificationType::PROPOSED, $user1->getProposedAudiobooks()->getId(), NotificationUserType::ADMIN);
        $this->databaseMockManager->testFunc_addNotifications([$user1,
            $user2], NotificationType::USER_DELETE_DECLINE, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);

        $this->databaseMockManager->testFunc_addNotificationCheck($user1, $notification1);

        /// step 2
        $content = [
            'page' => 0,
            'limit' => 10,
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 3
        self::$webClient->request('POST', '/api/notifications', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5
        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('systemNotifications', $responseContent);
        $this->assertArrayHasKey('page', $responseContent);
        $this->assertArrayHasKey('limit', $responseContent);
        $this->assertArrayHasKey('maxPage', $responseContent);
        $this->assertCount(3, $responseContent['systemNotifications']);
        $this->assertNotNull($responseContent['systemNotifications'][0]['active']);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_notificationsEmptyRequestData(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', notActive: true);
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');

        /// step 2
        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 3
        self::$webClient->request('POST', '/api/notifications', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad permission
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_notificationsPermission(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx', notActive: true);
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');

        /// step 2
        $content = [
            'page' => 0,
            'limit' => 10,
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 3
        self::$webClient->request('POST', '/api/notifications', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without token
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_notificationsLogOut(): void
    {
        /// step 1
        /// step 2
        $content = [
            'page' => 0,
            'limit' => 10,
        ];

        /// step 3
        self::$webClient->request('POST', '/api/notifications', content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
