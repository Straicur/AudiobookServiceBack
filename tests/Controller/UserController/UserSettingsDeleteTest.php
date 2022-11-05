<?php

namespace App\Tests\Controller\UserController;

use App\Tests\AbstractWebTest;

/**
 * UserSettingsDeleteTest
 */
class UserSettingsDeleteTest extends AbstractWebTest
{
//    /**
//     * step 1 - Preparing data
//     * step 2 - Preparing JsonBodyContent
//     * step 3 - Sending Request
//     * step 4 - Checking response
//     * step 5 - Checking response if category was
//     * @return void
//     */
//    public function test_userSettingsDeleteCorrect(): void
//    {
////        $audiobookInfoRepository = $this->getService(AudiobookInfoRepository::class);
////
////        $this->assertInstanceOf(AudiobookInfoRepository::class, $audiobookInfoRepository);
//        /// step 1
//        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
//
//
//        /// step 2
//        $content = [];
//
//        $token = $this->databaseMockManager->testFunc_loginUser($user);
//        /// step 3
//        $crawler = self::$webClient->request("PUT", "/api/user/audiobook/info/add", server: [
//            "HTTP_authorization" => $token->getToken()
//        ], content: json_encode($content));
//
//        /// step 4
//        $this->assertResponseIsSuccessful();
//        $this->assertResponseStatusCodeSame(200);
//
////        $this->assertCount(1, $audiobookInfoRepository->findAll());
//    }
//
//    /**
//     * step 1 - Preparing data
//     * step 2 - Preparing JsonBodyContent with bad CategoryKey
//     * step 3 - Sending Request
//     * step 4 - Checking response
//     *
//     * @return void
//     */
//    public function test_userSettingsDeleteIncorrectCategoryKey(): void
//    {
//        /// step 1
//
//        /// step 4
//        $this->assertResponseStatusCodeSame(404);
//
//        $responseContent = self::$webClient->getResponse()->getContent();
//
//        $this->assertNotNull($responseContent);
//        $this->assertNotEmpty($responseContent);
//        $this->assertJson($responseContent);
//
//        $responseContent = json_decode($responseContent, true);
//
//        $this->assertIsArray($responseContent);
//        $this->assertArrayHasKey("error", $responseContent);
//        $this->assertArrayHasKey("data", $responseContent);
//    }
//    /**
//     * step 1 - Preparing data
//     * step 2 - Sending Request without content
//     * step 3 - Checking response
//     *
//     * @return void
//     */
//    public function test_userSettingsDeleteEmptyRequestData(): void
//    {
//        /// step 1
//
//        /// step 3
//        $this->assertResponseStatusCodeSame(400);
//
//        $responseContent = self::$webClient->getResponse()->getContent();
//
//        $this->assertNotNull($responseContent);
//        $this->assertNotEmpty($responseContent);
//        $this->assertJson($responseContent);
//
//        $responseContent = json_decode($responseContent, true);
//
//        $this->assertIsArray($responseContent);
//        $this->assertArrayHasKey("error", $responseContent);
//    }
//
//    /**
//     * step 1 - Preparing data
//     * step 2 - Sending Request with bad permission
//     * step 3 - Checking response
//     *
//     * @return void
//     */
//    public function test_userSettingsDeletePermission(): void
//    {
//        /// step 1
//
//        /// step 3
//        $this->assertResponseStatusCodeSame(403);
//
//        $responseContent = self::$webClient->getResponse()->getContent();
//
//        $this->assertNotNull($responseContent);
//        $this->assertNotEmpty($responseContent);
//        $this->assertJson($responseContent);
//
//        $responseContent = json_decode($responseContent, true);
//
//        $this->assertIsArray($responseContent);
//        $this->assertArrayHasKey("error", $responseContent);
//    }
//
//    /**
//     * step 1 - Preparing data
//     * step 2 - Sending Request without token
//     * step 3 - Checking response
//     *
//     * @return void
//     */
//    public function test_userSettingsDeleteLogOut(): void
//    {
//        /// step 1
//
//        /// step 3
//        $this->assertResponseStatusCodeSame(401);
//
//        $responseContent = self::$webClient->getResponse()->getContent();
//
//        $this->assertNotNull($responseContent);
//        $this->assertNotEmpty($responseContent);
//        $this->assertJson($responseContent);
//
//        $responseContent = json_decode($responseContent, true);
//
//        $this->assertIsArray($responseContent);
//        $this->assertArrayHasKey("error", $responseContent);
//    }
}