<?php

namespace App\Tests\Controller\UserController;

use App\Repository\UserPasswordRepository;
use App\Tests\AbstractWebTest;
use App\ValueGenerator\PasswordHashGenerator;

/**
 * UserResetPasswordConfirmTest
 */
class UserResetPasswordConfirmTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if password and editable flag has changed
     * @return void
     */
    public function test_userResetPasswordConfirmCorrect(): void
    {
        $userPasswordRepository = $this->getService(UserPasswordRepository::class);

        $this->assertInstanceOf(UserPasswordRepository::class, $userPasswordRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx", edited: true, editableDate: (new \DateTime("Now"))->modify("+1 month"));

        $passwordGenerator = new PasswordHashGenerator("zaq12WSX");

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        /// step 2
        $content = [
            "userId" => $user->getId(),
            "password" => "zaq12WSX",
        ];

        $newPassword = $passwordGenerator->generate();

        /// step 3
        $crawler = self::$webClient->request("POST", "/api/user/reset/password/confirm", content: json_encode($content));

        /// step 4
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        /// step 5
        $password = $userPasswordRepository->findOneBy([
            "user" => $user->getId()
        ]);

        $userAfter = $password->getUser();
        $this->assertSame($newPassword, $password->getPassword());
        $this->assertSame($userAfter->getEdited(), false);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad UserId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_userResetPasswordConfirmIncorrectId(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx", edited: true, editableDate: (new \DateTime("Now"))->modify("+1 month"));

        $passwordGenerator = new PasswordHashGenerator("zaq12WSX");

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $newPassword = $passwordGenerator->generate();
        /// step 2
        $content = [
            "userId" => "66666c4e-16e6-1ecc-9890-a7e8b0073d3b",
            "password" => "zaq12WSX",
        ];
        /// step 3
        $crawler = self::$webClient->request("POST", "/api/user/reset/password/confirm", content: json_encode($content));
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
     * step 2 - Preparing JsonBodyContent with bad user edit flag
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_userResetPasswordConfirmIncorrectUserEditFlag(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx", editableDate: (new \DateTime("Now"))->modify("-1 month"));

        $passwordGenerator = new PasswordHashGenerator("zaq12WSX");

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $newPassword = $passwordGenerator->generate();
        /// step 2
        $content = [
            "userId" => $user->getId(),
            "password" => "zaq12WSX",
        ];
        /// step 3
        $crawler = self::$webClient->request("POST", "/api/user/reset/password/confirm", content: json_encode($content));
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
     * step 2 - Preparing JsonBodyContent with bad EditableDate
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_userResetPasswordConfirmIncorrectUserEditableDate(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx", edited: true, editableDate: (new \DateTime("Now"))->modify("-1 month"));

        $passwordGenerator = new PasswordHashGenerator("zaq12WSX");

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $newPassword = $passwordGenerator->generate();
        /// step 2
        $content = [
            "userId" => $user->getId(),
            "password" => "zaq12WSX",
        ];
        /// step 3
        $crawler = self::$webClient->request("POST", "/api/user/reset/password/confirm", content: json_encode($content));
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
    public function test_userResetPasswordConfirmEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx", edited: true, editableDate: (new \DateTime("Now"))->modify("+1 month"));

        $passwordGenerator = new PasswordHashGenerator("zaq12WSX");

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $newPassword = $passwordGenerator->generate();
        /// step 2
        $content = [];

        /// step 3
        $crawler = self::$webClient->request("POST", "/api/user/reset/password/confirm", content: json_encode($content));
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
}