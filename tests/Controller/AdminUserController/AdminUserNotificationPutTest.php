<?php

namespace App\Tests\Controller\AdminUserController;

use App\Enums\AudiobookAgeRange;
use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Repository\NotificationRepository;
use App\Tests\AbstractWebTest;

/**
 * AdminUserNotificationPutTest
 */
class AdminUserNotificationPutTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if notification was added
     * @return void
     */
    public function test_adminUserNotificationPutNORMALCorrect(): void
    {
        $notificationRepository = $this->getService(NotificationRepository::class);

        $this->assertInstanceOf(NotificationRepository::class, $notificationRepository);
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123127", ["Guest"], true, "zaq12wsx", notActive: true);
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123128", ["Guest", "User"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory("3", $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory("4", $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory("5", $category2, true);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d2", [$category2], active: true);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d3", [$category2], active: true);

        $audiobook4 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d4", [$category4, $category2], active: true);
        $audiobook5 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d5", [$category5], active: true);
        $audiobook6 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d6", [$category5], active: true);

        $this->databaseMockManager->testFunc_addProposedAudiobooks($user1, $audiobook1);

        /// step 2
        $content = [
            "notificationType" => NotificationType::NORMAL->value,
            "notificationUserType" => NotificationUserType::ADMIN->value,
            "additionalData" => [
                "text" => "Nowy text",
            ]
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 3
        $crawler = self::$webClient->request("PUT", "/api/admin/user/notification", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);
        /// step 5
        $this->assertCount(1, $notificationRepository->findAll());

    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if notification was added
     * @return void
     */
    public function test_adminUserNotificationPutADMINCorrect(): void
    {
        $notificationRepository = $this->getService(NotificationRepository::class);

        $this->assertInstanceOf(NotificationRepository::class, $notificationRepository);
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123128", ["Guest"], true, "zaq12wsx", notActive: true);
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123127", ["Guest", "User"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory("3", $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory("4", $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory("5", $category2, true);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d2", [$category2], active: true);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d3", [$category2], active: true);

        $audiobook4 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d4", [$category4, $category2], active: true);
        $audiobook5 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d5", [$category5], active: true);
        $audiobook6 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d6", [$category5], active: true);

        $this->databaseMockManager->testFunc_addProposedAudiobooks($user1, $audiobook1);

        /// step 2
        $content = [
            "notificationType" => NotificationType::ADMIN->value,
            "notificationUserType" => NotificationUserType::ADMIN->value,
            "additionalData" => [
                "text" => "Nowy text",
                "userId" => $user1->getId(),
            ]
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 3
        $crawler = self::$webClient->request("PUT", "/api/admin/user/notification", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);
        /// step 5
        $this->assertCount(1, $notificationRepository->findAll());

    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if notification was added
     * @return void
     */
    public function test_adminUserNotificationPutNEW_CATEGORYCorrect(): void
    {
        $notificationRepository = $this->getService(NotificationRepository::class);

        $this->assertInstanceOf(NotificationRepository::class, $notificationRepository);
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123128", ["Guest"], true, "zaq12wsx", notActive: true);
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123127", ["Guest", "User"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory("3", $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory("4", $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory("5", $category2, true);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d2", [$category2], active: true);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d3", [$category2], active: true);

        $audiobook4 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d4", [$category4, $category2], active: true);
        $audiobook5 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d5", [$category5], active: true);
        $audiobook6 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d6", [$category5], active: true);

        $this->databaseMockManager->testFunc_addProposedAudiobooks($user1, $audiobook1);

        /// step 2
        $content = [
            "notificationType" => NotificationType::NEW_CATEGORY->value,
            "notificationUserType" => NotificationUserType::ADMIN->value,
            "additionalData" => [
                "text" => "Nowy text",
                "categoryKey" => $category5->getCategoryKey(),
                "userId" => $user1->getId(),
            ]
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 3
        $crawler = self::$webClient->request("PUT", "/api/admin/user/notification", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);
        /// step 5
        $this->assertCount(1, $notificationRepository->findAll());

    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if notification was added
     * @return void
     */
    public function test_adminUserNotificationPutNEW_AUDIOBOOKCorrect(): void
    {
        $notificationRepository = $this->getService(NotificationRepository::class);

        $this->assertInstanceOf(NotificationRepository::class, $notificationRepository);
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123128", ["Guest"], true, "zaq12wsx", notActive: true);
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123127", ["Guest", "User"], true, "zaq12wsx");
        $user4 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test4@cos.pl", "+48123123126", ["Guest", "User"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory("3", $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory("4", $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory("5", $category2, true);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d2", [$category2], active: true);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d3", [$category2], active: true);

        $audiobook4 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d4", [$category4, $category2], active: true);
        $audiobook5 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d5", [$category5], active: true);
        $audiobook6 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d6", [$category5], active: true);

        $this->databaseMockManager->testFunc_addProposedAudiobooks($user1, $audiobook1);
        $this->databaseMockManager->testFunc_addProposedAudiobooks($user4, $audiobook1);
        $this->databaseMockManager->testFunc_addProposedAudiobooks($user2, $audiobook2);
        $this->databaseMockManager->testFunc_addProposedAudiobooks($user2, $audiobook5);
        $this->databaseMockManager->testFunc_addProposedAudiobooks($user3, $audiobook6);

        /// step 2
        $content = [
            "notificationType" => NotificationType::NEW_AUDIOBOOK->value,
            "notificationUserType" => NotificationUserType::ADMIN->value,
            "additionalData" => [
                "text" => "Nowy text",
                "actionId" => $audiobook2->getId(),
                "userId" => $user1->getId(),
            ]
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 3
        $crawler = self::$webClient->request("PUT", "/api/admin/user/notification", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);
        /// step 5
        $this->assertCount(1, $notificationRepository->findAll());

    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * @return void
     */
    public function test_adminUserNotificationPutIncorrectCorrectNotificationType(): void
    {
        $notificationRepository = $this->getService(NotificationRepository::class);

        $this->assertInstanceOf(NotificationRepository::class, $notificationRepository);
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123128", ["Guest"], true, "zaq12wsx", notActive: true);
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123127", ["Guest", "User"], true, "zaq12wsx");

        $userDelete = $this->databaseMockManager->testFunc_addUserDelete($user2);

        /// step 2
        $content = [
            "notificationType" => NotificationType::USER_DELETE_DECLINE->value,
            "notificationUserType" => NotificationUserType::SYSTEM->value,
            "additionalData" => [
                "text" => "Nowy text",
                "actionId" => $userDelete->getId(),
                "userId" => $user1->getId(),
            ]
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 3
        $crawler = self::$webClient->request("PUT", "/api/admin/user/notification", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));
        /// step 4
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
     * step 2 - Preparing JsonBodyContent with bad Admin user
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminUserNotificationPutADMINIncorrectUserId(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123127", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123126", ["Guest", "User"], true, "zaq12wsx");

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 2
        $content = [
            "notificationType" => NotificationType::ADMIN->value,
            "notificationUserType" => NotificationUserType::ADMIN->value,
            "additionalData" => [
                "text" => "Nowy text",
                "userId" => "66666c4e-16e6-1ecc-9890-a7e8b0073d3b",
            ]
        ];

        /// step 3
        $crawler = self::$webClient->request("PUT", "/api/admin/user/notification", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));
        /// step 4
        self::assertResponseStatusCodeSame(404);

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
     * step 2 - Preparing JsonBodyContent with bad userId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminUserNotificationPutIncorrectADMINEmptyUserId(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx", notActive: true);
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123127", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123126", ["Guest", "User"], true, "zaq12wsx");

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 2
        $content = [
            "notificationType" => NotificationType::ADMIN->value,
            "notificationUserType" => NotificationUserType::ADMIN->value,
            "additionalData" => [
                "text" => "Nowy text",
            ]
        ];

        /// step 3
        $crawler = self::$webClient->request("PUT", "/api/admin/user/notification", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));
        /// step 4
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
     * step 2 - Preparing JsonBodyContent with bad actionId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminUserNotificationPutIncorrectNEW_CATEGORYemptyCategoryKey(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx", notActive: true);
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123127", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123126", ["Guest", "User"], true, "zaq12wsx");

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        /// step 2
        $content = [
            "notificationType" => NotificationType::NEW_CATEGORY->value,
            "notificationUserType" => NotificationUserType::ADMIN->value,
            "additionalData" => [
                "text" => "Nowy text",
                "userId" => $user1->getId(),
            ]
        ];

        /// step 3
        $crawler = self::$webClient->request("PUT", "/api/admin/user/notification", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));
        /// step 4
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
     * step 2 - Preparing JsonBodyContent with bad Admin user
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminUserNotificationPutNEW_AUDIOBOOKIncorrectAudiobookId(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123177", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123126", ["Guest", "User"], true, "zaq12wsx");

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 2
        $content = [
            "notificationType" => NotificationType::NEW_AUDIOBOOK->value,
            "notificationUserType" => NotificationUserType::ADMIN->value,
            "additionalData" => [
                "text" => "Nowy text",
                "actionId" => "66666c4e-16e6-1ecc-9890-a7e8b0073d3b",
                "userId" => $user1->getId(),
            ]
        ];

        /// step 3
        $crawler = self::$webClient->request("PUT", "/api/admin/user/notification", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));
        /// step 4
        self::assertResponseStatusCodeSame(404);

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
     * step 2 - Preparing JsonBodyContent with bad actionId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminUserNotificationPutIncorrectNEW_AUDIOBOOKEmptyActionId(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx", notActive: true);
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123128", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123127", ["Guest", "User"], true, "zaq12wsx");

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 2
        $content = [
            "notificationType" => NotificationType::NEW_AUDIOBOOK->value,
            "notificationUserType" => NotificationUserType::ADMIN->value,
            "additionalData" => [
                "text" => "Nowy text",
                "userId" => $user1->getId(),
            ]
        ];

        /// step 3
        $crawler = self::$webClient->request("PUT", "/api/admin/user/notification", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));
        /// step 4
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
     * step 2 - Preparing JsonBodyContent with bad actionId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminUserNotificationPutIncorrectUSER_DELETE_DECLINEmptyActionId(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx", notActive: true);
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123128", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123127", ["Guest", "User"], true, "zaq12wsx");

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        /// step 2
        $content = [
            "notificationType" => NotificationType::USER_DELETE_DECLINE->value,
            "notificationUserType" => NotificationUserType::SYSTEM->value,
            "additionalData" => [
                "text" => "Nowy text",
                "userId" => $user1->getId(),
            ]
        ];
        /// step 3
        $crawler = self::$webClient->request("PUT", "/api/admin/user/notification", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));
        /// step 4
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
     * step 2 - Preparing JsonBodyContent with bad actionId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminUserNotificationPutIncorrectUSER_DELETE_DECLINEmptyUserId(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx", notActive: true);
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123128", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123127", ["Guest", "User"], true, "zaq12wsx");

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        $userDelete = $this->databaseMockManager->testFunc_addUserDelete($user2);

        /// step 2
        $content = [
            "notificationType" => NotificationType::USER_DELETE_DECLINE->value,
            "notificationUserType" => NotificationUserType::SYSTEM->value,
            "additionalData" => [
                "text" => "Nowy text",
                "actionId" => $userDelete->getId(),
            ]
        ];

        /// step 3
        $crawler = self::$webClient->request("PUT", "/api/admin/user/notification", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));
        /// step 4
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
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminUserNotificationPutEmptyRequestData(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx", notActive: true);
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123128", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123127", ["Guest", "User"], true, "zaq12wsx");

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 2
        $crawler = self::$webClient->request("PUT", "/api/admin/user/notification", server: [
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
    public function test_adminUserNotificationPutPermission(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx", notActive: true);
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123128", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123127", ["Guest", "User"], true, "zaq12wsx");

        $content = [
            "notificationType" => NotificationType::NEW_AUDIOBOOK->value,
            "notificationUserType" => NotificationUserType::ADMIN->value,
            "additionalData" => []
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 2
        $crawler = self::$webClient->request("PUT", "/api/admin/user/notification", server: [
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
    public function test_adminUserNotificationPutLogOut(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx", notActive: true);
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123128", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123127", ["Guest", "User"], true, "zaq12wsx");

        $content = [
            "notificationType" => NotificationType::NEW_AUDIOBOOK->value,
            "notificationUserType" => NotificationUserType::ADMIN->value,
            "additionalData" => []
        ];
        /// step 2
        $crawler = self::$webClient->request("PUT", "/api/admin/user/notification", content: json_encode($content));

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