<?php

namespace App\Tests\Controller\AdminAudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Repository\AudiobookRepository;
use App\Service\AudiobookService;
use App\Tests\AbstractWebTest;

/**
 * AdminAudiobookReAddingTest
 */
class AdminAudiobookReAddingTest extends AbstractWebTest
{
    private const base64OnePartFile = __DIR__ . "/onePartFile.txt";
    private const base64ReAddingPartFile = __DIR__ . "/reAddingPartFile.txt";
    private const base64FirstPartFile = __DIR__ . "/firstPartFile.txt";

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if audiobook is added and categories are correct
     * @return void
     */
    public function test_adminAudiobookAddCorrect(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);

        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1", null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $fileBase = fopen(self::base64OnePartFile, 'rb');
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
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $audiobookAfterFirst = $audiobookRepository->findOneBy([
            "title" => $content["additionalData"]['title']
        ]);

        $this->assertNotNull($audiobookAfterFirst);

        $fileBase = fopen(self::base64ReAddingPartFile, 'rb');
        $readData = fread($fileBase, filesize(self::base64ReAddingPartFile,));

        /// step 2
        $content = [
            "audiobookId" => $audiobookAfterFirst->getId(),
            "hashName" => "c91c03ea6c46a86cbc019be3d71d0a1a",
            "fileName" => "Base",
            "base64" => $readData,
            "part" => 1,
            "parts" => 1,
            "additionalData" => [
                "categories" => [
                    $category2->getId()
                ],
                "title" => "tytul2",
                "author" => "author2"
            ]
        ];
        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/admin/audiobook/reAdding", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $audiobookAfter = $audiobookRepository->findOneBy([
            "title" => $content["additionalData"]['title']
        ]);

        $this->assertNotNull($audiobookAfter);

        $this->assertSame($audiobookAfter->getTitle(), $content["additionalData"]['title']);
        $this->assertSame($audiobookAfter->getAuthor(), $content["additionalData"]['author']);
        $this->assertNotSame($audiobookAfterFirst->getParts(), $audiobookAfter->getParts());
        $this->assertNotSame($audiobookAfterFirst->getDescription(), $audiobookAfter->getDescription());
        $this->assertNotSame($audiobookAfterFirst->getSize(), $audiobookAfter->getSize());

        $hasSecondCategory = false;

        foreach ($audiobookAfter->getCategories() as $category) {
            if ($category->getId()->__toString() == $category2->getId()->__toString()) {
                $hasSecondCategory = true;
            }
        }

        $this->assertTrue($hasSecondCategory);

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if audiobook is added and categories are correct
     * @return void
     */
    public function test_adminAudiobookAddFirstPartCorrect(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);

        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1", null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $fileBase = fopen(self::base64OnePartFile, 'rb');
        $readData = fread($fileBase, filesize(self::base64OnePartFile,));

        /// step 2
        $content1 = [
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
        ], content: json_encode($content1));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $audiobookAfterFirst = $audiobookRepository->findOneBy([
            "title" => $content1["additionalData"]['title']
        ]);

        $this->assertNotNull($audiobookAfterFirst);

        $fileBase = fopen(self::base64FirstPartFile, 'rb');
        $readData = fread($fileBase, filesize(self::base64FirstPartFile,));

        /// step 2
        $content = [
            "audiobookId" => $audiobookAfterFirst->getId(),
            "hashName" => "c91c03ea6c46a86cbc019be3d71d0a1a",
            "fileName" => "Base",
            "base64" => $readData,
            "part" => 1,
            "parts" => 2,
            "additionalData" => [
                "categories" => [
                    $category2->getId()
                ],
                "title" => "tytul2",
                "author" => "author2"
            ]
        ];
        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/admin/audiobook/reAdding", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $audiobookAfter = $audiobookRepository->findOneBy([
            "title" => $content1["additionalData"]['title']
        ]);
        $this->assertNotNull($audiobookAfter);

        $audiobookService->removeFolder($audiobookAfter->getFileName());

        $audiobookService->removeFolder($_ENV['MAIN_DIR'] . "/" . $content["hashName"]);

    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if audiobook is added without additional data
     * @return void
     */
    public function test_adminAudiobookAddNoAdditionalDataCorrect(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);

        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        $fileBase = fopen(self::base64OnePartFile, 'rb');
        $readData = fread($fileBase, filesize(self::base64OnePartFile,));

        /// step 2
        $content = [
            "hashName" => "c91c03ea6c46a86cbc019be3d71d0a1a",
            "fileName" => "Base",
            "base64" => $readData,
            "part" => 1,
            "parts" => 1,
            "additionalData" => []
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("PUT", "/api/admin/audiobook/add", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $audiobookAfterFirst = $audiobookRepository->findAll()[0];

        $this->assertNotNull($audiobookAfterFirst);

        $fileBase = fopen(self::base64ReAddingPartFile, 'rb');
        $readData = fread($fileBase, filesize(self::base64ReAddingPartFile,));

        /// step 2
        $content = [
            "audiobookId" => $audiobookAfterFirst->getId(),
            "hashName" => "c91c03ea6c46a86cbc019be3d71d0a1a",
            "fileName" => "Base",
            "base64" => $readData,
            "part" => 1,
            "parts" => 1,
            "additionalData" => [
            ]
        ];
        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/admin/audiobook/reAdding", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $audiobookAfter = $audiobookRepository->findAll()[0];

        $this->assertNotNull($audiobookAfter);

        $this->assertNotSame($audiobookAfterFirst->getParts(), $audiobookAfter->getParts());
        $this->assertNotSame($audiobookAfterFirst->getDescription(), $audiobookAfter->getDescription());
        $this->assertNotSame($audiobookAfterFirst->getSize(), $audiobookAfter->getSize());

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }
//    /**
//     * step 1 - Preparing data
//     * step 2 - Preparing JsonBodyContent
//     * step 3 - Sending Request
//     * step 4 - Checking response
//     * step 5 - Checking response if audiobook is added and categories are correct
//     * @return void
//     */
//    public function test_adminAudiobookAddPartsCorrect(): void
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
//        self::assertResponseIsSuccessful();
//        self::assertResponseStatusCodeSame(201);
//
//        $audiobookAfterFirst = $audiobookRepository->findOneBy([
//            "title" => $content["additionalData"]['title']
//        ]);
//
//        $this->assertNotNull($audiobookAfterFirst);
//
//        $fileBase = fopen(self::base64FirstPartFile, "r");
//        $readData = fread($fileBase, filesize(self::base64FirstPartFile,));
//
//        /// step 2
//        $content = [
//            "audiobookId"=>$audiobookAfterFirst->getId(),
//            "hashName" => "c91c03ea6c46a86cbc019be3d71d0a1a",
//            "fileName" => "Base",
//            "base64" => $readData,
//            "part" => 1,
//            "parts" => 1,
//            "additionalData" => [
//                "categories" => [
//                    $category2->getId()
//                ],
//                "title" => "tytul2",
//                "author" => "author2"
//            ]
//        ];
//        /// step 3
//        $crawler = self::$webClient->request("PATCH", "/api/admin/audiobook/reAdding", server: [
//            "HTTP_authorization" => $token->getToken()
//        ], content: json_encode($content));
//
//        /// step 4
//        self::assertResponseIsSuccessful();
//        self::assertResponseStatusCodeSame(200);
//    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminAudiobookAddEmptyRequestData(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);

        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1", null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $fileBase = fopen(self::base64OnePartFile, 'rb');
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
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $audiobookAfterFirst = $audiobookRepository->findOneBy([
            "title" => $content["additionalData"]['title']
        ]);

        $this->assertNotNull($audiobookAfterFirst);

        $fileBase = fopen(self::base64ReAddingPartFile, 'rb');
        $readData = fread($fileBase, filesize(self::base64ReAddingPartFile,));

        /// step 2
        $content = [];
        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/admin/audiobook/reAdding", server: [
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

        $audiobookAfter = $audiobookRepository->findAll()[0];

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
    public function test_adminAudiobookAddPermission(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);

        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1", null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);
        $audiobookAfterFirst = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d6", [$category2]);

        $fileBase = fopen(self::base64ReAddingPartFile, 'rb');
        $readData = fread($fileBase, filesize(self::base64ReAddingPartFile,));

        /// step 2
        $content = [
            "audiobookId" => $audiobookAfterFirst->getId(),
            "hashName" => "c91c03ea6c46a86cbc019be3d71d0a1a",
            "fileName" => "Base",
            "base64" => $readData,
            "part" => 1,
            "parts" => 1,
            "additionalData" => [
                "categories" => [
                    $category2->getId()
                ],
                "title" => "tytul2",
                "author" => "author2"
            ]
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/admin/audiobook/reAdding", server: [
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
    public function test_adminAudiobookAddLogOut(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);

        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1", null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);
        $audiobookAfterFirst = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d6", [$category2]);

        $fileBase = fopen(self::base64OnePartFile, 'rb');
        $readData = fread($fileBase, filesize(self::base64OnePartFile,));

        $fileBase = fopen(self::base64ReAddingPartFile, 'rb');
        $readData = fread($fileBase, filesize(self::base64ReAddingPartFile,));

        /// step 2
        $content = [
            "audiobookId" => $audiobookAfterFirst->getId(),
            "hashName" => "c91c03ea6c46a86cbc019be3d71d0a1a",
            "fileName" => "Base",
            "base64" => $readData,
            "part" => 1,
            "parts" => 1,
            "additionalData" => [
                "categories" => [
                    $category2->getId()
                ],
                "title" => "tytul2",
                "author" => "author2"
            ]
        ];
        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/admin/audiobook/reAdding", content: json_encode($content));
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