<?php

namespace App\Tests\Controller\AudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Tests\AbstractWebTest;

/**
 * AudiobookCommentGetDetailTest
 */
class AudiobookCommentGetDetailTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response has returned correct data
     * @return void
     */
    public function test_audiobookCommentGetDetailCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment1", $audiobook1, $user);
        $comment2 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment2", $audiobook1, $user1);
        $comment3 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment3", $audiobook1, $user2);
        $comment4 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment4", $audiobook1, $user2, $comment1);
        $comment5 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment5", $audiobook1, $user1, $comment1);

        /// step 2
        $content = [
            "audiobookCommentId" => $comment1->getId(),
        ];
        /// step 2
        $crawler = self::$webClient->request("POST", "/api/audiobook/comment/get/detail", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5
        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey("audiobookCommentGetDetailModels", $responseContent);
        $this->assertCount(2, $responseContent["audiobookCommentGetDetailModels"]);
        $this->assertArrayHasKey("id", $responseContent["audiobookCommentGetDetailModels"][0]);
        $this->assertArrayHasKey("comment", $responseContent["audiobookCommentGetDetailModels"][0]);
        $this->assertArrayHasKey("edited", $responseContent["audiobookCommentGetDetailModels"][0]);
        $this->assertArrayHasKey("userModel", $responseContent["audiobookCommentGetDetailModels"][0]);
        $this->assertArrayHasKey("myComment", $responseContent["audiobookCommentGetDetailModels"][0]);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_audiobookCommentGetDetailEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment1", $audiobook1, $user);
        $comment2 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment2", $audiobook1, $user1);
        $comment3 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment3", $audiobook1, $user2);
        $comment4 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment3", $audiobook1, $user2, $comment1);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        /// step 2
        $content = [];

        /// step 2
        $crawler = self::$webClient->request("POST", "/api/audiobook/comment/get/detail", server: [
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
    public function test_audiobookCommentGetDetailIncorrectParentId(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment1", $audiobook1, $user);
        $comment2 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment2", $audiobook1, $user1);
        $comment3 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment3", $audiobook1, $user2);
        $comment4 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment3", $audiobook1, $user2, $comment1);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        /// step 2
        $content = [
            "audiobookCommentId" => "66666c4e-16e6-1ecc-9890-a7e8b0073d3b",
        ];

        /// step 2
        $crawler = self::$webClient->request("POST", "/api/audiobook/comment/get/detail", server: [
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
    public function test_audiobookCommentGetDetailPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest"], true, "zaq12wsx");
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment1", $audiobook1, $user);
        $comment2 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment2", $audiobook1, $user1);
        $comment3 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment3", $audiobook1, $user2);
        $comment4 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment4", $audiobook1, $user2, $comment1);
        $comment5 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment5", $audiobook1, $user1, $comment1);

        /// step 2
        $content = [
            "audiobookCommentId" => $comment1->getId(),
        ];
        /// step 2
        $crawler = self::$webClient->request("POST", "/api/audiobook/comment/get/detail", server: [
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
    public function test_audiobookCommentGetDetailLogOut(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment1", $audiobook1, $user);
        $comment2 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment2", $audiobook1, $user1);
        $comment3 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment3", $audiobook1, $user2);
        $comment4 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment4", $audiobook1, $user2, $comment1);
        $comment5 = $this->databaseMockManager->testFunc_addAudiobookUserComment("comment5", $audiobook1, $user1, $comment1);

        /// step 2
        $content = [
            "audiobookCommentId" => $comment1->getId(),
        ];
        /// step 2
        $crawler = self::$webClient->request("POST", "/api/audiobook/comment/get/detail", server: [], content: json_encode($content));

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