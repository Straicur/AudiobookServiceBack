<?php

namespace App\Tests\Controller\AdminUserController;

use App\Tests\AbstractWebTest;

/**
 * AdminUserSystemRolesTest
 */
class AdminUserSystemRolesTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response has returned correct data
     * @return void
     */
    public function test_adminUserSystemRolesCorrect(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        
        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 2
        $crawler = self::$webClient->request("GET", "/api/admin/user/system/roles", server: [
            "HTTP_authorization" => $token->getToken()
        ]);

        /// step 3
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 4
        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey("roles", $responseContent);
        $this->assertCount(2, $responseContent["roles"]);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad permission
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminUserSystemRolesPermission(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User",], true, "zaq12wsx", notActive: true);

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 2
        $crawler = self::$webClient->request("GET", "/api/admin/user/system/roles", server: [
            "HTTP_authorization" => $token->getToken()
        ]);

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
     * step 1 - Sending Request without token
     * step 2 - Checking response
     *
     * @return void
     */
    public function test_adminUserSystemRolesLogOut(): void
    {

        /// step 1
        $crawler = self::$webClient->request("GET", "/api/admin/user/system/roles");

        /// step2
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