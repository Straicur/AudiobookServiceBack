<?php

namespace App\Tests\Controller\AdminAudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Tests\AbstractWebTest;

/**
 * AdminAudiobookDetailsTest
 */
class AdminAudiobookDetailsTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response has returned correct data
     * @return void
     */
    public function test_adminAudiobookDetailsCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,[$category1,$category2],active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,[$category2]);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,[$category2]);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $content = [
            "audiobookId" => $audiobook1->getId(),
        ];
        /// step 2
        $crawler = self::$webClient->request("POST", "/api/admin/audiobook/details", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5
        $this->assertIsArray($responseContent);
        print_r($responseContent);
        $this->assertArrayHasKey("categories", $responseContent);
    }
     /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad AudiobookId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_AdminAudiobookDetailsIncorrectAudiobookId(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,[$category1,$category2],active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,[$category2]);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,[$category2]);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $content = [
            "audiobookId" => "66666c4e-16e6-1ecc-9890-a7e8b0073d3b",
        ];
        /// step 2
        $crawler = self::$webClient->request("POST", "/api/admin/audiobook/details", server: [
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
    public function test_AdminAudiobookDetailsEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,[$category1,$category2]);
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,[$category2]);
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,[$category2]);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,[$category1,$category2],active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,[$category2]);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,[$category2]);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $content = [];

        /// step 2
        $crawler = self::$webClient->request("POST", "/api/admin/audiobook/details", server: [
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
    public function test_adminAudiobookDetailsPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,[$category1,$category2],active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,[$category2]);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,[$category2]);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $content = [
            "audiobookId" => $audiobook1->getId(),
        ];
        /// step 2
        $crawler = self::$webClient->request("POST", "/api/admin/audiobook/details", server: [
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
    public function test_adminAudiobookDetailsLogOut(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,[$category1,$category2],active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,[$category2]);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook("t","a","2","d",new \DateTime("Now"),"20","20",2,"desc",AudiobookAgeRange::ABOVE18,[$category2]);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $content = [
            "audiobookId" => $audiobook1->getId(),
        ];
        /// step 2
        $crawler = self::$webClient->request("POST", "/api/admin/audiobook/details", content: json_encode($content));

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