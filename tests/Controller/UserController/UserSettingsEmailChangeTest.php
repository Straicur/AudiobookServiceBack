<?php

namespace App\Tests\Controller\UserController;

use App\Repository\UserRepository;
use App\Tests\AbstractWebTest;

/**
 * UserSettingsEmailChangeTest
 */
class UserSettingsEmailChangeTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Checking response
     * step 4 - Checking response if all data has changed
     * @return void
     */
    public function test_userSettingsEmailCorrect(): void
    {
        $userRepository = $this->getService(UserRepository::class);

        $this->assertInstanceOf(UserRepository::class, $userRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx",edited: true,editableDate: (new \DateTime("Now"))->modify("+1 month"));

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $crawler = self::$webClient->request("GET", "/api/user/settings/email/change/test2@cos.pl/".$user->getId()->__toString());

        /// step 3
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $userAfter = $userRepository->findOneBy([
            "id"=>$user->getId()
        ]);
        /// step 4
        $this->assertSame($userAfter->getUserInformation()->getEmail(), "test2@cos.pl");
        $this->assertFalse($userAfter->getEdited());
    }
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad Editable date
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userSettingsEmailIncorrectEditableDate(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx",edited: true,editableDate: (new \DateTime("Now"))->modify("-1 month"));

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $crawler = self::$webClient->request("GET", "/api/user/settings/email/change/test2@cos.pl/".$user->getId()->__toString());
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
     * step 2 - Sending Request with bad EditFlag
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userSettingsEmailIncorrectEditFlag(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx",editableDate: (new \DateTime("Now"))->modify("-1 month"));

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $crawler = self::$webClient->request("GET", "/api/user/settings/email/change/test2@cos.pl/".$user->getId()->__toString());
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
     * step 2 - Sending Request with bad CategoryKey
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userSettingsEmailIncorrectUserId(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx",edited: true,editableDate: (new \DateTime("Now"))->modify("+1 month"));

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $crawler = self::$webClient->request("GET", "/api/user/settings/email/change/test2@cos.pl/66666c4e-16e6-1ecc-9890-a7e8b0073d3b");
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
     * step 2 - Sending Request with bad CategoryKey
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userSettingsEmailIncorrectEmail(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx",edited: true,editableDate: (new \DateTime("Now"))->modify("+1 month"));

        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $crawler = self::$webClient->request("GET", "/api/user/settings/email/change/test2@cos.pl/".$user->getId()->__toString());
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
    public function test_userSettingsEmailEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx",edited: true,editableDate: (new \DateTime("Now"))->modify("+1 month"));

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $crawler = self::$webClient->request("GET", "/api/user/settings/email/change//");
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