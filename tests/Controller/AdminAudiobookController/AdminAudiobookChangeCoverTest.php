<?php

namespace App\Tests\Controller\AdminAudiobookController;

use App\Repository\AudiobookRepository;
use App\Service\AudiobookService;
use App\Tests\AbstractWebTest;

/**
 * AdminAudiobookChangeCoverTest
 */
class AdminAudiobookChangeCoverTest extends AbstractWebTest
{
    private const base64OnePartFile = __DIR__ . "/onePartFile.txt";
    private const base64ImgFile = __DIR__ . "/imgFile.txt";
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if audiobook is added and categories are correct
     * @return void
     */
    public function test_adminAudiobookZipCorrect(): void
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

        $fileBase2 = fopen(self::base64ImgFile, "r");
        $readData2 = fread($fileBase2, filesize(self::base64ImgFile,));

        $content2 = [
            "type"=>"jpeg",
            "base64"=>$readData2,
            "audiobookId" => $audiobookAfter->getId(),
        ];

        $dir = $audiobookAfter->getFileName();

        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/admin/audiobook/change/cover", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content2));

        /// step 4
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }

//    /**
//     * step 1 - Preparing data
//     * step 2 - Sending Request without content
//     * step 3 - Checking response
//     *
//     * @return void
//     */
//    public function test_adminAudiobookZipWrongAudiobookId(): void
//    {
//        $audiobookRepository = $this->getService(AudiobookRepository::class);
//        $audiobookService = $this->getService(AudiobookService::class);
//
//        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
//        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
//        /// step 1
//        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
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
//        $content2 = [
//            "audiobookId" => "66666c4e-16e6-1ecc-9890-a7e8b0073d3b",
//        ];
//
//        $dir = $audiobookAfter->getFileName();
//
//        /// step 3
//        $crawler = self::$webClient->request("POST", "/api/admin/audiobook/zip", server: [
//            "HTTP_authorization" => $token->getToken()
//        ], content: json_encode($content2));
//        /// step 3
//        $this->assertResponseStatusCodeSame(404);
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
//        $this->assertArrayHasKey("data", $responseContent);
//
//
//        $response = self::$webClient->getResponse();
//
//        /// step 5
//        $audiobookAfter = $audiobookRepository->findOneBy([
//            "title" => $content["additionalData"]['title']
//        ]);
//
//        $this->assertNotNull($audiobookAfter);
//
//        $audiobookService->removeFolder($audiobookAfter->getFileName());
//    }
//
//    /**
//     * step 1 - Preparing data
//     * step 2 - Sending Request without content
//     * step 3 - Checking response
//     *
//     * @return void
//     */
//    public function test_adminAudiobookZipEmptyRequestData(): void
//    {
//        $audiobookRepository = $this->getService(AudiobookRepository::class);
//        $audiobookService = $this->getService(AudiobookService::class);
//
//        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
//        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
//        /// step 1
//        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
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
//        $content2 = [];
//
//        $dir = $audiobookAfter->getFileName();
//
//        /// step 3
//        $crawler = self::$webClient->request("POST", "/api/admin/audiobook/zip", server: [
//            "HTTP_authorization" => $token->getToken()
//        ], content: json_encode($content2));
//        /// step 3
//        $this->assertResponseStatusCodeSame(400);
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
//
//
//        $response = self::$webClient->getResponse();
//
//        /// step 5
//        $audiobookAfter = $audiobookRepository->findOneBy([
//            "title" => $content["additionalData"]['title']
//        ]);
//
//        $this->assertNotNull($audiobookAfter);
//
//        $audiobookService->removeFolder($audiobookAfter->getFileName());
//    }
//    /**
//     * step 1 - Preparing data
//     * step 2 - Sending Request with bad permission
//     * step 3 - Checking response
//     *
//     * @return void
//     */
//    public function test_adminAudiobookZipPermission(): void
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
//        $crawler = self::$webClient->request("POST", "/api/admin/audiobook/zip", server: [
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
//    /**
//     * step 1 - Preparing data
//     * step 2 - Sending Request without token
//     * step 3 - Checking response
//     *
//     * @return void
//     */
//    public function test_adminAudiobookZipLogOut(): void
//    {
//        $audiobookRepository = $this->getService(AudiobookRepository::class);
//        $audiobookService = $this->getService(AudiobookService::class);
//
//        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
//        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
//        /// step 1
//        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
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
//        $content2 = [
//            "audiobookId" => $audiobookAfter->getId(),
//        ];
//
//        $dir = $audiobookAfter->getFileName();
//
//        /// step 3
//
//        $crawler = self::$webClient->request("POST", "/api/admin/audiobook/zip", content: json_encode($content2));
//        /// step 3
//        $this->assertResponseStatusCodeSame(401);
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
//
//        $response = self::$webClient->getResponse();
//
//        /// step 5
//        $audiobookAfter = $audiobookRepository->findOneBy([
//            "title" => $content["additionalData"]['title']
//        ]);
//
//        $this->assertNotNull($audiobookAfter);
//
//        $audiobookService->removeFolder($audiobookAfter->getFileName());
//    }
}