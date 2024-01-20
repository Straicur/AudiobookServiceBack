<?php

namespace App\Tests\Controller\AdminUserController;

use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Tests\AbstractWebTest;

/**
 * AdminUserNotificationsTest
 */
class AdminUserNotificationsTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response has returned correct data
     * @return void
     */
    public function test_adminUserNotificationsCorrect(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123128", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123127", ["Guest", "User"], true, "zaq12wsx");
        $user4 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test4@cos.pl", "+48123123126", ["Guest", "User"], true, "zaq12wsx");
        $user5 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test5@cos.pl", "+48123123125", ["Guest", "User"], true, "zaq12wsx");

        $this->databaseMockManager->testFunc_addNotifications([$user1,$user2,$user4], NotificationType::ADMIN, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);
        $this->databaseMockManager->testFunc_addNotifications([$user1,$user3,$user5], NotificationType::PROPOSED, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);
        $this->databaseMockManager->testFunc_addNotifications([$user1,$user2], NotificationType::USER_DELETE_DECLINE, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);

        /// step 2
        $content = [
            "page" => 0,
            "limit" => 10,
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 3
        $crawler = self::$webClient->request("POST", "/api/admin/user/notifications", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5
        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey("systemNotifications", $responseContent);
        $this->assertArrayHasKey("page", $responseContent);
        $this->assertArrayHasKey("limit", $responseContent);
        $this->assertArrayHasKey("maxPage", $responseContent);
        $this->assertCount(3, $responseContent["systemNotifications"]);
    }
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response has returned correct data
     * @return void
     */
    public function test_adminUserNotificationsSpecificSearchCorrect(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123125", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123126", ["Guest", "User"], true, "zaq12wsx");
        $user4 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test4@cos.pl", "+48123123127", ["Guest", "User"], true, "zaq12wsx");
        $user5 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test5@cos.pl", "+48123123128", ["Guest", "User"], true, "zaq12wsx");

        $this->databaseMockManager->testFunc_addNotifications([$user1, $user2, $user4], NotificationType::NORMAL, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM, "t1");
        $this->databaseMockManager->testFunc_addNotifications([$user1, $user3, $user5], NotificationType::PROPOSED, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM, "t2");
        $this->databaseMockManager->testFunc_addNotifications([$user1, $user2], NotificationType::USER_DELETE_DECLINE, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM, "t3", true);
        $this->databaseMockManager->testFunc_addNotifications([$user1, $user2], NotificationType::NORMAL, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM, "t4", true);

        /// step 2
        $content = [
            "page" => 0,
            "limit" => 10,
            "searchData" => [
                "text" => "t",
                "type" => 1,
                "deleted" => false,
                "order" => 1
            ]
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 3
        $crawler = self::$webClient->request("POST", "/api/admin/user/notifications", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5
        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey("systemNotifications", $responseContent);
        $this->assertArrayHasKey("page", $responseContent);
        $this->assertArrayHasKey("limit", $responseContent);
        $this->assertArrayHasKey("maxPage", $responseContent);
        $this->assertCount(1, $responseContent["systemNotifications"]);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminUserNotificationsEmptyRequestData(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx", notActive: true);
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123125", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123126", ["Guest", "User"], true, "zaq12wsx");
        $user4 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test4@cos.pl", "+48123123127", ["Guest", "User"], true, "zaq12wsx");
        $user5 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test5@cos.pl", "+48123123128", ["Guest", "User"], true, "zaq12wsx");

        /// step 2
        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 3
        $crawler = self::$webClient->request("POST", "/api/admin/user/notifications", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(400);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey("error", $responseContent);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad permission
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminUserNotificationsPermission(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User",], true, "zaq12wsx", notActive: true);
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123125", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123126", ["Guest", "User"], true, "zaq12wsx");
        $user4 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test4@cos.pl", "+48123123127", ["Guest", "User"], true, "zaq12wsx");
        $user5 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test5@cos.pl", "+48123123128", ["Guest", "User"], true, "zaq12wsx");

        /// step 2
        $content = [
            "page" => 0,
            "limit" => 10,
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 3
        $crawler = self::$webClient->request("POST", "/api/admin/user/notifications", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(403);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey("error", $responseContent);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without token
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminUserNotificationsLogOut(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx", notActive: true);
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123125", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123126", ["Guest", "User"], true, "zaq12wsx");
        $user4 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test4@cos.pl", "+48123123127", ["Guest", "User"], true, "zaq12wsx");
        $user5 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test5@cos.pl", "+48123123128", ["Guest", "User"], true, "zaq12wsx");

        /// step 2
        $content = [
            "page" => 0,
            "limit" => 10,
        ];

        /// step 3
        $crawler = self::$webClient->request("POST", "/api/admin/user/notifications", content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(401);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey("error", $responseContent);
    }
}