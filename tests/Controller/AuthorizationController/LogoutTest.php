<?php

namespace App\Tests\Controller\AuthorizationController;

use App\Tests\AbstractWebTest;

/**
 * LogoutTest
 */
class LogoutTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if responseContent has key token
     * @return void
     */
    public function test_logoutCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@asuri.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");
        /// step 2
        $content = [
            "email" => "test@asuri.pl",
            "password" => "zaq12wsx"
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("POST", "/api/logout", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));
        /// step 4
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

    }

    /**
     * step 1 - Sending Request without token
     * step 2 - Checking response
     * @return void
     */
    public function test_logoutLogOut(): void
    {
        /// step 1
        $crawler = self::$webClient->request("POST", "/api/logout");
        /// step 2
        $this->assertResponseStatusCodeSame(401);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);
    }
}
