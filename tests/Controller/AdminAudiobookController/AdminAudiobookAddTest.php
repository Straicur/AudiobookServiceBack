<?php

namespace App\Tests\Controller\AdminAudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Repository\AudiobookRepository;
use App\Service\AudiobookService;
use App\Tests\AbstractWebTest;

/**
 * AdminAudiobookAddTest
 */
class AdminAudiobookAddTest extends AbstractWebTest
{
    private const base64OnePartFile = __DIR__ . "/onePartFile.txt";

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if category is active
     * @return void
     */
    public function test_adminAudiobookActiveCorrect(): void
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
                    $category2->getId()
                ],
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

        $audiobookService->removeAudiobook($content["fileName"]);
    }
}