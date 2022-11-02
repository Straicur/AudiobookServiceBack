<?php

namespace App\Tests\Controller\UserAudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Tests\AbstractWebTest;

/**
 * UserAudiobooksTest
 */
class UserAudiobooksTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response has returned correct data
     * @return void
     */
    public function test_userAudiobooksCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory("3", $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory("4", $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory("5", $category2, true);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d1", [$category1,$category2],active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d2", [$category2]);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d3", [$category2]);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            "page" => 0,
            "limit" => 10
        ];

        /// step 2
        $crawler = self::$webClient->request("POST", "/api/user/audiobooks", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5
        $this->assertIsArray($responseContent);

//        $this->assertArrayHasKey("categories", $responseContent);
//        $this->assertCount(1, $responseContent["categories"]);
//
//        $this->assertCount(2, $responseContent["categories"][0]["children"]);
    }
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userAudiobookInfoEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory("3", $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory("4", $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory("5", $category2, true);
        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d1", [$category1,$category2],active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d2", [$category2]);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d3", [$category2]);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [];

        /// step 2
        $crawler = self::$webClient->request("POST", "/api/user/audiobooks", server: [
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
    public function test_userAudiobooksPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory("3", $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory("4", $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory("5", $category2, true);
        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d1", [$category1,$category2],active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d2", [$category2]);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d3", [$category2]);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        $content = [
            "page" => 0,
            "limit" => 10
        ];


        /// step 2
        $crawler = self::$webClient->request("POST", "/api/user/audiobooks", server: [
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
    public function test_userAudiobooksLogOut(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory("3", $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory("4", $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory("5", $category2, true);
        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d1", [$category1,$category2],active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d2", [$category2]);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,  "d3", [$category2]);

        $content = [
            "page" => 0,
            "limit" => 10
        ];

        /// step 2
        $crawler = self::$webClient->request("POST", "/api/user/audiobooks", content: json_encode($content));

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