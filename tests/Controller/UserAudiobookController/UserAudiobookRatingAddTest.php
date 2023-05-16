<?php

namespace App\Tests\Controller\UserAudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Repository\AudiobookRatingRepository;
use App\Tests\AbstractWebTest;
use DateTime;

/**
 * UserAudiobookRatingGetTest
 */
class UserAudiobookRatingAddTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking if rating was added
     * @return void
     */
    public function test_userAudiobookRatingAddCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t1", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);

        $audiobookInfo1 = $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook1, 1, "dsa", false, true);
        $audiobookInfo2 = $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook1, 2, "dsa", false, true);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            "audiobookId" => $audiobook1->getId(),
            "categoryKey" => $category2->getCategoryKey(),
            "rating" => true
        ];

        /// step 2
        $crawler = self::$webClient->request("PUT", "/api/user/audiobook/rating/add", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5
        $audiobookRatingRepository = $this->getService(AudiobookRatingRepository::class);

        $this->assertInstanceOf(AudiobookRatingRepository::class, $audiobookRatingRepository);

        $this->assertCount(1, $audiobookRatingRepository->findAll());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userAudiobookRatingAddEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t1", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);

        $audiobookInfo1 = $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook1, 1, "dsa", false, true);
        $audiobookInfo2 = $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook1, 2, "dsa", false, true);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [];

        /// step 2
        $crawler = self::$webClient->request("PUT", "/api/user/audiobook/rating/add", server: [
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
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userAudiobookRatingAddBadAudiobookInfos(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t1", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);

        $audiobookInfo1 = $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook1, 1, "dsa", false, true);
        $audiobookInfo2 = $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook1, 2, "dsa", false);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            "audiobookId" => $audiobook1->getId(),
            "categoryKey" => $category2->getCategoryKey(),
            "rating" => true
        ];

        /// step 2
        $crawler = self::$webClient->request("PUT", "/api/user/audiobook/rating/add", server: [
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
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userAudiobookRatingAddBadAudiobookId(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t1", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);

        $audiobookInfo1 = $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook1, 1, "dsa", false, true);
        $audiobookInfo2 = $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook1, 2, "dsa", false, true);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            "audiobookId" => "66666c4e-16e6-1ecc-9890-a7e8b0073d3b",
            "categoryKey" => $category2->getCategoryKey(),
            "rating" => true
        ];

        /// step 2
        $crawler = self::$webClient->request("PUT", "/api/user/audiobook/rating/add", server: [
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
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userAudiobookRatingAddBadCategoryKey(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t1", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);

        $audiobookInfo1 = $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook1, 1, "dsa", false, true);
        $audiobookInfo2 = $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook1, 2, "dsa", false, true);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            "audiobookId" => $audiobook1->getId(),
            "categoryKey" => "66666c4e-16e6-1ecc-9890-a7e8b0073d3b",
            "rating" => true
        ];

        /// step 2
        $crawler = self::$webClient->request("PUT", "/api/user/audiobook/rating/add", server: [
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
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad permission
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userAudiobookRatingAddPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t1", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);

        $audiobookInfo1 = $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook1, 1, "dsa", false, true);
        $audiobookInfo2 = $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook1, 2, "dsa", false, true);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            "audiobookId" => $audiobook1->getId(),
            "categoryKey" => $category2->getCategoryKey(),
            "rating" => true
        ];

        /// step 2
        $crawler = self::$webClient->request("PUT", "/api/user/audiobook/rating/add", server: [
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
    public function test_userAudiobookRatingAddLogOut(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t1", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);

        $audiobookInfo1 = $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook1, 1, "dsa", false, true);
        $audiobookInfo2 = $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook1, 2, "dsa", false, true);

        $content = [
            "audiobookId" => $audiobook1->getId(),
            "categoryKey" => $category2->getCategoryKey(),
            "rating" => true
        ];

        /// step 2
        $crawler = self::$webClient->request("PUT", "/api/user/audiobook/rating/add", content: json_encode($content));

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