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
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response if password and editable flag has changed
     * @return void
     */
    public function test_userResetPasswordConfirmCorrect(): void
    {
        $userPasswordRepository = $this->getService(UserPasswordRepository::class);

        $this->assertInstanceOf(UserPasswordRepository::class, $userPasswordRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx",edited: true,editableDate: (new \DateTime("Now"))->modify("+1 month"));

        $passwordGenerator = new PasswordHashGenerator("zaq12WSX");

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $newPassword = $passwordGenerator->generate();

        /// step 2
        $crawler = self::$webClient->request("GET", "/api/user/reset/password/confirm/".$user->getId()->__toString()."/".$newPassword);

        /// step 3
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        /// step 4
        $password = $userPasswordRepository->findOneBy([
            "user"=>$user->getId()
        ]);

        $userAfter = $password->getUser();

        $this->assertSame($newPassword, $password->getPassword());
        $this->assertSame($userAfter->getEdited(), false);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad UserId
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userResetPasswordConfirmIncorrectId(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx",edited: true,editableDate: (new \DateTime("Now"))->modify("+1 month"));

        $passwordGenerator = new PasswordHashGenerator("zaq12WSX");

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $newPassword = $passwordGenerator->generate();

        /// step 2
        $crawler = self::$webClient->request("GET", "/api/user/reset/password/confirm/66666c4e-16e6-1ecc-9890-a7e8b0073d3b/".$newPassword);
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
     * step 2 - Sending Request without Pass
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userResetPasswordConfirmEmptyPass(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx",edited: true,editableDate: (new \DateTime("Now"))->modify("+1 month"));

        $passwordGenerator = new PasswordHashGenerator("zaq12WSX");

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $newPassword = $passwordGenerator->generate();

        /// step 2
        $crawler = self::$webClient->request("GET", "/api/user/reset/password/confirm/".$user->getId()->__toString()."/");
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
     * step 2 - Sending Request with bad user edit flag
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userResetPasswordConfirmIncorrectUserEditFlag(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx",editableDate: (new \DateTime("Now"))->modify("-1 month"));

        $passwordGenerator = new PasswordHashGenerator("zaq12WSX");

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $newPassword = $passwordGenerator->generate();

        /// step 2
        $crawler = self::$webClient->request("GET", "/api/user/reset/password/confirm/".$user->getId()->__toString()."/".$newPassword);
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
     * step 2 - Sending Request with bad EditableDate
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userResetPasswordConfirmIncorrectUserEditableDate(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx",edited: true,editableDate: (new \DateTime("Now"))->modify("-1 month"));

        $passwordGenerator = new PasswordHashGenerator("zaq12WSX");

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $newPassword = $passwordGenerator->generate();

        /// step 2
        $crawler = self::$webClient->request("GET", "/api/user/reset/password/confirm/".$user->getId()->__toString()."/".$newPassword);
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
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userResetPasswordConfirmEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx",edited: true,editableDate: (new \DateTime("Now"))->modify("+1 month"));

        $passwordGenerator = new PasswordHashGenerator("zaq12WSX");

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $newPassword = $passwordGenerator->generate();

        /// step 2
        $crawler = self::$webClient->request("GET", "/api/user/reset/password/confirm//");
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
}