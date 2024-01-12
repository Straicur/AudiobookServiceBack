<?php

namespace App\Tests\Controller\UserSettingsController;

use App\Repository\UserRepository;
use App\Tests\AbstractWebTest;

/**
 * UserParentControlPatchTest
 */
class UserParentControlPatchTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if all data has changed
     * @return void
     */
    public function test_userParentControlPatchCorrect(): void
    {
        $userRepository = $this->getService(UserRepository::class);

        $this->assertInstanceOf(UserRepository::class, $userRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $userParentalControlCode = $this->databaseMockManager->testFunc_addUserParentalControlCode($user);

        /// step 2
        $content = [
            "smsCode" => $userParentalControlCode->getCode(),
            "additionalData" => [
            ],
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/user/parent/control", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $userAfter = $userRepository->findOneBy([
            "id" => $user->getId()
        ]);

        /// step 5
        $this->assertNull($userAfter->getUserInformation()->getBirthday());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if all data has changed
     * @return void
     */
    public function test_userParentControlPatchBirthdayCorrect(): void
    {
        $userRepository = $this->getService(UserRepository::class);

        $this->assertInstanceOf(UserRepository::class, $userRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $userParentalControlCode = $this->databaseMockManager->testFunc_addUserParentalControlCode($user);
        /// step 2
        $content = [
            "smsCode" => $userParentalControlCode->getCode(),
            "additionalData" => [
                "birthday" => "01.09.1998",
            ],
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/user/parent/control", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $birthdayDate = \DateTime::createFromFormat('d.m.Y', "01.09.1998");

        $userAfter = $userRepository->findOneBy([
            "id" => $user->getId()
        ]);

        /// step 5
        $this->assertSame($userAfter->getUserInformation()->getBirthday()->getTimestamp(), $birthdayDate->getTimestamp());
    }

    /**
     * /**
     *  step 1 - Preparing data
     *  step 2 - Preparing JsonBodyContent with bad SmsCode
     *  step 3 - Sending Request
     *  step 4 - Checking response
     *
     * @return void
     */
    public function test_userSettingsIncorrectSmsCode(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123121", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        /// step 2
        $content = [
            "smsCode" => "A2312V4",
            "additionalData" => [
                "birthday" => "01.09.1998",
            ],
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/user/parent/control", server: [
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
    public function test_userParentControlPatchEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/user/parent/control", server: [
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
    public function test_userParentControlPatchPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest"], true, "zaq12wsx");

        $content = [
            "smsCode" => "A2312V4",
            "additionalData" => [
                "birthday" => "01.09.1998",
            ],
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/user/parent/control", server: [
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
    public function test_userParentControlPatchLogOut(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $content = [
            "smsCode" => "A2312V4",
            "additionalData" => [
                "birthday" => "01.09.1998",
            ],
        ];

        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/user/parent/control", content: json_encode($content));
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