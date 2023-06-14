<?php

namespace App\Tests\Controller\UserController;

use App\Repository\UserRepository;
use App\Tests\AbstractWebTest;

/**
 * UserSettingsGetTest
 */
class UserSettingsGetTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if all data has changed
     * @return void
     */
    public function test_userSettingsGetCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("GET", "/api/user/settings", server: [
            "HTTP_authorization" => $token->getToken()
        ]);

        /// step 4
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5
        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey("phoneNumber",$responseContent);
        $this->assertSame($user->getUserInformation()->getPhoneNumber(),$responseContent["phoneNumber"]);
        $this->assertArrayHasKey("firstname",$responseContent);
        $this->assertSame($user->getUserInformation()->getFirstname(),$responseContent["firstname"]);
        $this->assertArrayHasKey("lastname",$responseContent);
        $this->assertSame($user->getUserInformation()->getLastname(),$responseContent["lastname"]);
        $this->assertArrayHasKey("email",$responseContent);
        $this->assertSame($user->getUserInformation()->getEmail(),$responseContent["email"]);
        $this->assertArrayHasKey("edited",$responseContent);
        $this->assertSame($user->getEdited(),$responseContent["edited"]);
        $this->assertArrayHasKey("editableDate",$responseContent);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad permission
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userSettingsGetPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest"], true, "zaq12wsx");

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $crawler = self::$webClient->request("GET", "/api/user/settings", server: [
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
     * step 1 - Preparing data
     * step 2 - Sending Request without token
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userSettingsGetLogOut(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        /// step 2
        $crawler = self::$webClient->request("GET", "/api/user/settings");
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