<?php

namespace App\Tests\Controller\AdminAudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Repository\AudiobookCategoryRepository;
use App\Repository\AudiobookRepository;
use App\Tests\AbstractWebTest;

/**
 * AdminAudiobookEditTest
 */
class AdminAudiobookEditTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if category is active
     * @return void
     */
    public function test_adminAudiobookEditCorrect(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);

        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1", null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18,  "d",  [$category1, $category2], null, (new \DateTime("Now"))->modify("- 1 month"), active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18,  "d1",  [$category2], active: true);

        /// step 2
        $content = [
            "audiobookId" => $audiobook1->getId(),
            "title" => "ty",
            "author" => "au",
            "version" => "3",
            "album" => "da",
            "year" => "27.11.2022",
            "duration" => "30",
            "size" => "30",
            "parts" => 3,
            "description" => "Desc",
            "age" => AudiobookAgeRange::FROM7TO12->value,
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/admin/audiobook/edit", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $audiobook1After = $audiobookRepository->findOneBy([
            "id" => $audiobook1->getId()
        ]);

        $this->assertSame($audiobook1After->getTitle(),$content["title"]);
        $this->assertSame($audiobook1After->getAuthor(),$content["author"]);
        $this->assertSame($audiobook1After->getVersion(),$content["version"]);
        $this->assertSame($audiobook1After->getAlbum(),$content["album"]);
        $this->assertSame($audiobook1After->getYear()->format('d.m.Y'),$content["year"]);
        $this->assertSame($audiobook1After->getDuration(),$content["duration"]);
        $this->assertSame($audiobook1After->getSize(),$content["size"]);
        $this->assertSame($audiobook1After->getParts(),$content["parts"]);
        $this->assertSame($audiobook1After->getDescription(),$content["description"]);
        $this->assertSame($audiobook1After->getAge()->value,$content["age"]);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad audiobookId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminAudiobookEditIncorrectAudiobookId(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory("3", $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory("4", $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory("5", $category2, true);
        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18,  "d",  [$category1, $category2], null, (new \DateTime("Now"))->modify("- 1 month"), active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18,  "d1",  [$category2], active: true);
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            "audiobookId" => "66666c4e-16e6-1ecc-9890-a7e8b0073d3b",
            "title" => "ty",
            "author" => "au",
            "version" => "3",
            "album" => "da",
            "year" => "27.11.2022",
            "duration" => "30",
            "size" => "30",
            "parts" => "3",
            "description" => "Desc",
            "age" => AudiobookAgeRange::FROM7TO12->value,
        ];

        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/admin/audiobook/edit", server: [
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
    public function test_adminAudiobookEditEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory("3", $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory("4", $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory("5", $category2, true);
        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18,  "d",  [$category1, $category2], null, (new \DateTime("Now"))->modify("- 1 month"), active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18,  "d1",  [$category2], active: true);
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [];

        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/admin/audiobook/edit", server: [
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
    public function test_adminAudiobookEditPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory("3", $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory("4", $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory("5", $category2, true);
        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18,  "d",  [$category1, $category2], null, (new \DateTime("Now"))->modify("- 1 month"), active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18,  "d1",  [$category2], active: true);
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            "audiobookId" => $category1->getId(),
            "title" => "ty",
            "author" => "au",
            "version" => "3",
            "album" => "da",
            "year" => "27.11.2022",
            "duration" => "30",
            "size" => "30",
            "parts" => "3",
            "description" => "Desc",
            "age" => AudiobookAgeRange::FROM7TO12->value,
        ];

        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/admin/audiobook/edit", server: [
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
    public function test_adminAudiobookEditLogOut(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory("3", $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory("4", $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory("5", $category2, true);
        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18,  "d",  [$category1, $category2], null, (new \DateTime("Now"))->modify("- 1 month"), active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18,  "d1",  [$category2], active: true);
        /// step 2
        $content = [
            "audiobookId" => $category1->getId(),
            "title" => "ty",
            "author" => "au",
            "version" => "3",
            "album" => "da",
            "year" => "27.11.2022",
            "duration" => "30",
            "size" => "30",
            "parts" => "3",
            "description" => "Desc",
            "age" => AudiobookAgeRange::FROM7TO12->value,
        ];

        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/admin/audiobook/edit", content: json_encode($content));

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