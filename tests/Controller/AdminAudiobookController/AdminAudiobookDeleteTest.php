<?php

namespace App\Tests\Controller\AdminAudiobookController;

use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Repository\AudiobookRepository;
use App\Repository\NotificationRepository;
use App\Service\AudiobookService;
use App\Tests\AbstractWebTest;

/**
 * AdminAudiobookDeleteTest
 */
class AdminAudiobookDeleteTest extends AbstractWebTest
{
    private const base64OnePartFile = __DIR__ . "/onePartFile.txt";

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if audiobook is added and categories are correct
     * @return void
     */
    public function test_adminAudiobookDeleteCorrect(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $notificationRepository = $this->getService(NotificationRepository::class);

        $this->assertInstanceOf(NotificationRepository::class, $notificationRepository);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1", null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $fileBase = fopen(self::base64OnePartFile, "r");
        $readData = fread($fileBase, filesize(self::base64OnePartFile));

        /// step 2
        $content = [
            "hashName" => "c91c03ea6c46a86cbc019be3d71d0a1a",
            "fileName" => "Base",
            "base64" => $readData,
            "part" => 1,
            "parts" => 1,
            "additionalData" => [
                "categories" => [
                    $category2->getId(),
                    $category1->getId()
                ],
                "title" => "tytul",
                "author" => "author"
            ]
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("PUT", "/api/admin/audiobook/add", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);

        $audiobookAfter = $audiobookRepository->findOneBy([
            "title" => $content["additionalData"]['title']
        ]);

        $this->assertNotNull($audiobookAfter);

        $notification1 = $this->databaseMockManager->testFunc_addNotifications([$user1, $user2], NotificationType::NEW_CATEGORY, $audiobookAfter->getId(), NotificationUserType::SYSTEM);
        $notification2 = $this->databaseMockManager->testFunc_addNotifications([$user1, $user2], NotificationType::NEW_AUDIOBOOK, $audiobookAfter->getId(), NotificationUserType::SYSTEM);

        $this->databaseMockManager->testFunc_addNotificationCheck($user1, $notification2);

        $content = [
            "audiobookId" => $audiobookAfter->getId(),
        ];

        $dir = $audiobookAfter->getFileName();

        /// step 3
        $crawler = self::$webClient->request("DELETE", "/api/admin/audiobook/delete", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $this->assertNull($notificationRepository->findOneBy([
            "id" => $notification1->getId()
        ]));

        $audiobookAfter = $audiobookRepository->findAll();

        $this->assertCount(0, $audiobookAfter);

        $this->assertFalse(is_dir($dir));

    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminAudiobookDeleteWrongAudiobookId(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);

        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1", null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $fileBase = fopen(self::base64OnePartFile, "r");
        $readData = fread($fileBase, filesize(self::base64OnePartFile,));

        /// step 2
        $content = [
            "hashName" => "c91c03ea6c46a86cbc019be3d71d0a1a",
            "fileName" => "Base",
            "base64" => $readData,
            "part" => 1,
            "parts" => 1,
            "additionalData" => [
                "categories" => [
                    $category2->getId(),
                    $category1->getId()
                ],
                "title" => "tytul",
                "author" => "author"
            ]
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("PUT", "/api/admin/audiobook/add", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);

        $audiobookAfter = $audiobookRepository->findOneBy([
            "title" => $content["additionalData"]['title']
        ]);

        $this->assertNotNull($audiobookAfter);

        $content = [
            "audiobookId" => "66666c4e-16e6-1ecc-9890-a7e8b0073d3b",
        ];

        $dir = $audiobookAfter->getFileName();

        /// step 3
        $crawler = self::$webClient->request("DELETE", "/api/admin/audiobook/delete", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        $this->assertResponseStatusCodeSame(404);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey("error", $responseContent);
        $this->assertArrayHasKey("data", $responseContent);

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminAudiobookDeleteEmptyRequestData(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);

        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1", null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $fileBase = fopen(self::base64OnePartFile, "r");
        $readData = fread($fileBase, filesize(self::base64OnePartFile,));

        /// step 2
        $content = [
            "hashName" => "c91c03ea6c46a86cbc019be3d71d0a1a",
            "fileName" => "Base",
            "base64" => $readData,
            "part" => 1,
            "parts" => 1,
            "additionalData" => [
                "categories" => [
                    $category2->getId(),
                    $category1->getId()
                ],
                "title" => "tytul",
                "author" => "author"
            ]
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("PUT", "/api/admin/audiobook/add", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);

        $audiobookAfter = $audiobookRepository->findOneBy([
            "title" => $content["additionalData"]['title']
        ]);

        $this->assertNotNull($audiobookAfter);

        $content = [];

        $dir = $audiobookAfter->getFileName();

        /// step 3
        $crawler = self::$webClient->request("DELETE", "/api/admin/audiobook/delete", server: [
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

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }
//    /**
//     * step 1 - Preparing data
//     * step 2 - Sending Request with bad permission
//     * step 3 - Checking response
//     *
//     * @return void
//     */
//    public function test_adminAudiobookDeletePermission(): void
//    {
//        $audiobookRepository = $this->getService(AudiobookRepository::class);
//
//        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
//        /// step 1
//        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
//        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "tes3@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");
//        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "tesr4@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");
//
//        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1", null, true);
//        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);
//
//        $fileBase = fopen(self::base64OnePartFile, "r");
//        $readData = fread($fileBase, filesize(self::base64OnePartFile,));
//
//        /// step 2
//        $content = [
//            "hashName" => "c91c03ea6c46a86cbc019be3d71d0a1a",
//            "fileName" => "Base",
//            "base64" => $readData,
//            "part" => 1,
//            "parts" => 1,
//            "additionalData" => [
//                "categories" => [
//                    $category2->getId(),
//                    $category1->getId()
//                ],
//                "title" => "tytul",
//                "author" => "author"
//            ]
//        ];
//        $token = $this->databaseMockManager->testFunc_loginUser($user);
//        /// step 3
//        $crawler = self::$webClient->request("PUT", "/api/admin/audiobook/add", server: [
//            "HTTP_authorization" => $token->getToken()
//        ], content: json_encode($content));
//
//        /// step 4
//        $this->assertResponseIsSuccessful();
//        $this->assertResponseStatusCodeSame(201);
//
//        $audiobookAfter = $audiobookRepository->findOneBy([
//            "title" => $content["additionalData"]['title']
//        ]);
//
//        $this->assertNotNull($audiobookAfter);
//
//        $content = [
//            "audiobookId" => $audiobookAfter->getId(),
//        ];
//
//        $dir = $audiobookAfter->getFileName();
//
//        $token = $this->databaseMockManager->testFunc_loginUser($user2);
//
//        /// step 3
//        $crawler = self::$webClient->request("DELETE", "/api/admin/audiobook/delete", server: [
//            "HTTP_authorization" => $token->getToken()
//        ], content: json_encode($content));
//        /// step 3
//        $this->assertResponseStatusCodeSame(403);
//
//        $responseContent = self::$webClient->getResponse()->getContent();
//
//        $this->assertNotNull($responseContent);
//        $this->assertNotEmpty($responseContent);
//        $this->assertJson($responseContent);
//
//        $responseContent = json_decode($responseContent, true);
//
//        $this->assertIsArray($responseContent);
//        $this->assertArrayHasKey("error", $responseContent);
//    }
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without token
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminAudiobookDeleteLogOut(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);

        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1", null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $fileBase = fopen(self::base64OnePartFile, "r");
        $readData = fread($fileBase, filesize(self::base64OnePartFile,));

        /// step 2
        $content = [
            "hashName" => "c91c03ea6c46a86cbc019be3d71d0a1a",
            "fileName" => "Base",
            "base64" => $readData,
            "part" => 1,
            "parts" => 1,
            "additionalData" => [
                "categories" => [
                    $category2->getId(),
                    $category1->getId()
                ],
                "title" => "tytul",
                "author" => "author"
            ]
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("PUT", "/api/admin/audiobook/add", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);

        $audiobookAfter = $audiobookRepository->findOneBy([
            "title" => $content["additionalData"]['title']
        ]);

        $this->assertNotNull($audiobookAfter);

        $content = [
            "audiobookId" => $audiobookAfter->getId(),
        ];

        $dir = $audiobookAfter->getFileName();

        /// step 3

        $crawler = self::$webClient->request("DELETE", "/api/admin/audiobook/delete", content: json_encode($content));
        /// step 3
        $this->assertResponseStatusCodeSame(401);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey("error", $responseContent);

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }

}