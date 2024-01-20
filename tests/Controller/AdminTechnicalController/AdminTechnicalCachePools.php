<?php

namespace App\Tests\Controller\AdminTechnicalController;

use App\Tests\AbstractWebTest;

/**
 * AdminTechnicalCachePools
 */
class AdminTechnicalCachePools extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if category is active
     * @return void
     */
    public function test_adminTechnicalCachePoolsCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("POST", "/api/admin/technical/cache/pools", server: [
            "HTTP_authorization" => $token->getToken()
        ]);

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);
        print_r($responseContent);
//        $this->assertArrayHasKey("technicalBreaks", $responseContent);
//        $this->assertArrayHasKey("page", $responseContent);
//        $this->assertArrayHasKey("limit", $responseContent);
//        $this->assertArrayHasKey("maxPage", $responseContent);
//        $this->assertCount(2, $responseContent["technicalBreaks"]);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad permission
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminTechnicalCachePoolsPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        /// step 2
        $crawler = self::$webClient->request("POST", "/api/admin/technical/cache/pools", server: [
            "HTTP_authorization" => $token->getToken()
        ]);
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
    public function test_adminTechnicalCachePoolsLogOut(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        /// step 2
        $crawler = self::$webClient->request("POST", "/api/admin/technical/cache/pools");

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