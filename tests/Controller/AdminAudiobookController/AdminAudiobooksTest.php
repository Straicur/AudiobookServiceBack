<?php

namespace App\Tests\Controller\AdminAudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Tests\AbstractWebTest;

/**
 * AdminAudiobooksTest
 */
class AdminAudiobooksTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response has returned correct data
     * @return void
     */
    public function test_adminAudiobooksCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d1",[$category1,$category2],null,(new \DateTime("Now"))->modify("- 1 month"));
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d2", [$category2]);
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d3", [$category2],null,(new \DateTime("Now"))->modify("- 1 year"));
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d4", [$category1,$category2],null,(new \DateTime("Now"))->modify("- 1 month"));
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d5", [$category2]);
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d6", [$category2],null,(new \DateTime("Now"))->modify("- 1 year"));
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d7", [$category1,$category2],null,(new \DateTime("Now"))->modify("- 1 month"));
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d8", [$category2]);
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d9", [$category2],null,(new \DateTime("Now"))->modify("- 1 year"));
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d10", [$category1,$category2],null,(new \DateTime("Now"))->modify("- 1 month"));
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d11", [$category2]);
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d12", [$category2],null,(new \DateTime("Now"))->modify("- 1 year"));

        /// step 2
        $content = [
            "page" => 1,
            "limit" => 10,
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("POST", "/api/admin/audiobooks", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);


        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5
        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey("audiobooks", $responseContent);
        $this->assertCount(2, $responseContent["audiobooks"]);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminAudiobooksEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d1", [$category1,$category2]);
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d2", [$category2]);
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d3", [$category2]);

        /// step 2
        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("POST", "/api/admin/audiobooks", server: [
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
    public function test_adminAudiobooksPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d1", [$category1,$category2]);
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d2", [$category2]);
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d3", [$category2]);

        /// step 2
        $content = [
            "page" => 0,
            "limit" => 10,
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("POST", "/api/admin/audiobooks", server: [
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
    public function test_adminAudiobooksLogOut(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d1", [$category1,$category2]);
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d2", [$category2]);
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d3", [$category2]);

        /// step 2
        $content = [
            "page" => 0,
            "limit" => 10,
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("POST", "/api/admin/audiobooks", content: json_encode($content));

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