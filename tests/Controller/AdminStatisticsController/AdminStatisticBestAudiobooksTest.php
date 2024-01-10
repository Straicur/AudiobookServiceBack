<?php

namespace App\Tests\Controller\AdminStatisticsController;

use App\Enums\AudiobookAgeRange;
use App\Tests\AbstractWebTest;

/**
 * AdminStatisticBestAudiobooksTest
 */
class AdminStatisticBestAudiobooksTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response has returned correct data
     * @return void
     */
    public function test_adminStatisticBestAudiobooksCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123129", ["Guest", "User", "Administrator"], true, "zaq12wsx",(new \DateTime("now"))->modify("-9 day"));
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123128", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123127", ["Guest", "User", "Administrator"], true, "zaq12wsx",notActive: true);
        $user4 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test4@cos.pl", "+48123123126", ["Guest", "User", "Administrator"], true, "zaq12wsx",(new \DateTime("now"))->modify("-9 day"));
        $user5 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test5@cos.pl", "+48123123125", ["Guest", "User", "Administrator"], true, "zaq12wsx",(new \DateTime("now"))->modify("-9 day"));

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d2", [$category2], active: true);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d3", [$category2], active: true);
        $audiobook4 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d4", [$category2], active: true);
        $audiobook5 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d5", [$category2]);

        $audiobookRating1 = $this->databaseMockManager->testFunc_addAudiobookRating($audiobook1, true, $user1);
        $audiobookRating2 = $this->databaseMockManager->testFunc_addAudiobookRating($audiobook1, true, $user2);
        $audiobookRating3 = $this->databaseMockManager->testFunc_addAudiobookRating($audiobook1, false, $user3);

        $audiobookRating4 = $this->databaseMockManager->testFunc_addAudiobookRating($audiobook2, true, $user1);
        $audiobookRating5 = $this->databaseMockManager->testFunc_addAudiobookRating($audiobook2, true, $user2);
        $audiobookRating6 = $this->databaseMockManager->testFunc_addAudiobookRating($audiobook2, false, $user3);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2

        /// step 2
        $crawler = self::$webClient->request("GET", "/api/admin/statistic/best/audiobooks", server: [
            "HTTP_authorization" => $token->getToken()
        ]);

        /// step 3
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5
        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey("firstAudiobook", $responseContent);
        $this->assertArrayHasKey("secondAudiobook", $responseContent);
        $this->assertArrayHasKey("thirdAudiobook", $responseContent);

        $this->assertArrayHasKey("id", $responseContent["firstAudiobook"]);
        $this->assertArrayHasKey("title", $responseContent["firstAudiobook"]);
        $this->assertArrayHasKey("author", $responseContent["firstAudiobook"]);
        $this->assertArrayHasKey("version", $responseContent["firstAudiobook"]);
        $this->assertArrayHasKey("album", $responseContent["firstAudiobook"]);
        $this->assertArrayHasKey("year", $responseContent["firstAudiobook"]);
        $this->assertArrayHasKey("duration", $responseContent["firstAudiobook"]);
        $this->assertArrayHasKey("size", $responseContent["firstAudiobook"]);
        $this->assertArrayHasKey("parts", $responseContent["firstAudiobook"]);
        $this->assertArrayHasKey("description", $responseContent["firstAudiobook"]);
        $this->assertArrayHasKey("age", $responseContent["firstAudiobook"]);
        $this->assertArrayHasKey("active", $responseContent["firstAudiobook"]);
        $this->assertArrayHasKey("categories", $responseContent["firstAudiobook"]);
        $this->assertCount(2, $responseContent["firstAudiobook"]["categories"]);
    }
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without token
     * step 3 - Checking response
     * @return void
     */
    public function test_adminStatisticBestAudiobooksLogout(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123129", ["Guest", "User", "Administrator"], true, "zaq12wsx",(new \DateTime("now"))->modify("-9 day"));
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123128", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123127", ["Guest", "User", "Administrator"], true, "zaq12wsx",notActive: true);
        $user4 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test4@cos.pl", "+48123123126", ["Guest", "User", "Administrator"], true, "zaq12wsx",(new \DateTime("now"))->modify("-9 day"));
        $user5 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test5@cos.pl", "+48123123125", ["Guest", "User", "Administrator"], true, "zaq12wsx",(new \DateTime("now"))->modify("-9 day"));

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d2", [$category2], active: true);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d3", [$category2], active: true);
        $audiobook4 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d4", [$category2], active: true);
        $audiobook5 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d5", [$category2]);

        $audiobookRating1 = $this->databaseMockManager->testFunc_addAudiobookRating($audiobook1, true, $user1);
        $audiobookRating2 = $this->databaseMockManager->testFunc_addAudiobookRating($audiobook1, true, $user2);
        $audiobookRating3 = $this->databaseMockManager->testFunc_addAudiobookRating($audiobook1, false, $user3);

        $audiobookRating4 = $this->databaseMockManager->testFunc_addAudiobookRating($audiobook2, true, $user1);
        $audiobookRating5 = $this->databaseMockManager->testFunc_addAudiobookRating($audiobook2, true, $user2);
        $audiobookRating6 = $this->databaseMockManager->testFunc_addAudiobookRating($audiobook2, false, $user3);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2

        /// step 2
        $crawler = self::$webClient->request("GET", "/api/admin/statistic/best/audiobooks");
        
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