<?php

namespace App\Tests\Controller\AdminUserController;

use App\Repository\UserPasswordRepository;
use App\Tests\AbstractWebTest;
use App\ValueGenerator\PasswordHashGenerator;

/**
 * AdminUserChangePasswordTest
 */
class AdminUserChangePasswordTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if password changed
     * @return void
     */
    public function test_adminUserDetailsCorrect(): void
    {
        $userPasswordRepository = $this->getService(UserPasswordRepository::class);

        $this->assertInstanceOf(UserPasswordRepository::class, $userPasswordRepository);
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsX", notActive: true);
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");

        /// step 2
        $content = [
            "userId" => $user2->getId(),
            "newPassword" => "zaq12wsx"
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/admin/user/change/password", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        /// step 5
        $passwordGenerator = new PasswordHashGenerator($content["newPassword"]);

        $user2PasswordAfter = $userPasswordRepository->findOneBy([
            "user" => $user2->getId()
        ]);

        $this->assertSame($passwordGenerator->generate(), $user2PasswordAfter->getPassword());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad Admin user
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminUserDetailsIncorrectAdminUser(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 2
        $content = [
            "userId" => $user1->getId(),
            "newPassword" => "zaq12wsx"
        ];

        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/admin/user/change/password", server: [
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
     * step 2 - Preparing JsonBodyContent with bad userId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminUserDetailsIncorrectUserId(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx", notActive: true);
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 2
        $content = [
            "userId" => "66666c4e-16e6-1ecc-9890-a7e8b0073d3b",
            "newPassword" => "zaq12wsx"
        ];

        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/admin/user/change/password", server: [
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
    public function test_adminUserDetailsEmptyRequestData(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx", notActive: true);
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/admin/user/change/password", server: [
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
    public function test_adminUserDetailsPermission(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx", notActive: true);
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");

        $content = [
            "userId" => $user2->getId(),
            "newPassword" => "zaq12wsx"
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/admin/user/change/password", server: [
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
    public function test_adminUserDetailsLogOut(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx", notActive: true);
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");

        $content = [
            "userId" => $user2->getId(),
            "newPassword" => "zaq12wsx"
        ];
        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/admin/user/change/password", content: json_encode($content));

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