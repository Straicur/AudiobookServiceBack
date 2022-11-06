<?php

namespace App\Tests\Controller\AudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Repository\AudiobookRepository;
use App\Service\AudiobookService;
use App\Tests\AbstractWebTest;

/**
 * AudiobookCoverTest
 */
class AudiobookCoverTest extends AbstractWebTest
{
    private const base64OnePartFile = "/home/damian/ApkiPortfolio/InzRefactor/tests/Controller/AdminAudiobookController/onePartFile.txt";

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if audiobook is added and categories are correct
     * @return void
     */
    public function test_audiobookCoverCorrect(): void
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

        $content2 = [
            "audiobookId" => $audiobookAfter->getId(),
            "part" => 0
        ];

        $dir = $audiobookAfter->getFileName();

        /// step 3
        $crawler = self::$webClient->request("GET", "/api/audiobook/cover/" . $audiobookAfter->getId()->__toString(), server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content2));

        /// step 4
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_audiobookCoverWrongAudiobookId(): void
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


        $content2 = [
            "audiobookId" => "66666c4e-16e6-1ecc-9890-a7e8b0073d3b",
            "part" => 0
        ];

        $dir = $audiobookAfter->getFileName();

        /// step 3
        $crawler = self::$webClient->request("GET", "/api/audiobook/cover/66666c4e-16e6-1ecc-9890-a7e8b0073d3b", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content2));
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


        $response = self::$webClient->getResponse();

        /// step 5
        $audiobookAfter = $audiobookRepository->findOneBy([
            "title" => $content["additionalData"]['title']
        ]);

        $this->assertNotNull($audiobookAfter);

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_audiobookCoverEmptyRequestData(): void
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

        $content2 = [];

        $dir = $audiobookAfter->getFileName();

        /// step 3
        $crawler = self::$webClient->request("GET", "/api/audiobook/cover/", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content2));
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

        /// step 5
        $audiobookAfter = $audiobookRepository->findOneBy([
            "title" => $content["additionalData"]['title']
        ]);

        $this->assertNotNull($audiobookAfter);

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad permission
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_audiobookCoverPermission(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);

        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "tes3@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "tesr4@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1", null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $fileBase = fopen(self::base64OnePartFile, "r");
        $readData = fread($fileBase, filesize(self::base64OnePartFile,));
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d3", [$category2]);

        $content2 = [
            "audiobookId" => $audiobook->getId(),
            "part" => 0
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("GET", "/api/audiobook/cover/" . $audiobook->getId()->__toString(), server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content2));
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
    public function test_audiobookCoverLogOut(): void
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


        $content2 = [
            "audiobookId" => $audiobookAfter->getId(),
            "part" => 0
        ];

        $dir = $audiobookAfter->getFileName();

        /// step 3
        $crawler = self::$webClient->request("GET", "/api/audiobook/cover/" . $audiobookAfter->getId()->__toString(), content: json_encode($content2));
        /// step 3
        $this->assertResponseStatusCodeSame(401);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey("error", $responseContent);

        $response = self::$webClient->getResponse();

        /// step 5
        $audiobookAfter = $audiobookRepository->findOneBy([
            "title" => $content["additionalData"]['title']
        ]);

        $this->assertNotNull($audiobookAfter);

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }
}