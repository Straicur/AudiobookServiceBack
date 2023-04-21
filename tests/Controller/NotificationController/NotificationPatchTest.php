<?php

namespace App\Tests\Controller\NotificationController;

use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Repository\NotificationRepository;
use App\Tests\AbstractWebTest;

/**
 * NotificationPatchTest
 */
class NotificationPatchTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if negotiation flag changed
     * @return void
     */
    public function test_notificationPatchCorrect(): void
    {
        $notificationRepository = $this->getService(NotificationRepository::class);

        $this->assertInstanceOf(NotificationRepository::class, $notificationRepository);
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $not1 = $this->databaseMockManager->testFunc_addNotifications([$user1,$user2], NotificationType::ADMIN, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);

        /// step 2
        $content = [
            "notificationId" => $not1->getId()
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/notification", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $notAfter = $notificationRepository->findOneBy([
            "id" => $not1->getId()
        ]);

        $this->assertTrue($notAfter->getReadStatus());

    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad negotiationId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_notificationPatchIncorrectUserDeleted(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        /// step 2
        $content = [
            "notificationId" => "66666c4e-16e6-1ecc-9890-a7e8b0073d3b"
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/notification", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));
        /// step 4
        $this->assertResponseStatusCodeSame(404);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey("error", $responseContent);
        $this->assertArrayHasKey("data", $responseContent);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_notificationPatchEmptyRequestData(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $not1 = $this->databaseMockManager->testFunc_addNotifications([$user1,$user2], NotificationType::ADMIN, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);

        /// step 2
        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/notification", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        $this->assertResponseStatusCodeSame(400);

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
    public function test_notificationPatchPermission(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest"], true, "zaq12wsx");

        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $not1 = $this->databaseMockManager->testFunc_addNotifications([$user1,$user2], NotificationType::ADMIN, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);

        /// step 2
        $content = [
            "notificationId" => $not1->getId()
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/notification", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        $this->assertResponseStatusCodeSame(403);

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
    public function test_notificationPatchLogOut(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $not1 = $this->databaseMockManager->testFunc_addNotifications([$user1,$user2], NotificationType::ADMIN, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);

        /// step 2
        $content = [
            "notificationId" => $not1->getId()
        ];

        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/notification", content: json_encode($content));
        /// step 3
        $this->assertResponseStatusCodeSame(401);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey("error", $responseContent);
    }
}