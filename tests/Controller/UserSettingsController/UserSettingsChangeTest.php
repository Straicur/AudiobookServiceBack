<?php

namespace App\Tests\Controller\UserSettingsController;

use App\Repository\UserRepository;
use App\Tests\AbstractWebTest;

/**
 * UserSettingsChangeTest
 */
class UserSettingsChangeTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if all data has changed
     * @return void
     */
    public function test_userSettingsChangeCorrect(): void
    {
        $userRepository = $this->getService(UserRepository::class);

        $this->assertInstanceOf(UserRepository::class, $userRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        /// step 2
        $content = [
            "phoneNumber" => "+48124124124",
            "firstName" => "Damian",
            "lastName" => "Mos",
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/user/settings/change", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $userAfter = $userRepository->findOneBy([
            "id" => $user->getId()
        ]);

        /// step 5
        $this->assertNotSame($userAfter->getUserInformation()->getPhoneNumber(), '+48123123123');
        $this->assertNotSame($userAfter->getUserInformation()->getLastname(), "Test");
        $this->assertNotSame($userAfter->getUserInformation()->getFirstname(), "User");
    }

    /**
     * /**
     *  step 1 - Preparing data
     *  step 2 - Preparing JsonBodyContent with bad PhoneNumber
     *  step 3 - Sending Request
     *  step 4 - Checking response
     *
     * @return void
     */
    public function test_userSettingsIncorrectPhoneNumber(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123121", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        /// step 2
        $content = [
            "phoneNumber" => "+48123123121",
            "firstName" => "Damian",
            "lastName" => "Mos",
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/user/settings/change", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));
        /// step 4
        self::assertResponseStatusCodeSame(404);

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
    public function test_userSettingsChangeEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/user/settings/change", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(400);

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
    public function test_userSettingsChangePermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest"], true, "zaq12wsx");

        $content = [
            "phoneNumber" => "+48124124124",
            "firstName" => "Damian",
            "lastName" => "Mos",
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/user/settings/change", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));
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
    public function test_userSettingsChangeLogOut(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $content = [
            "phoneNumber" => "+48124124124",
            "firstName" => "Damian",
            "lastName" => "Mos",
        ];

        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/user/settings/change", content: json_encode($content));
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