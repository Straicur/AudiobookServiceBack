<?php

namespace App\Tests\Controller\RegisterController;

use App\Repository\RegisterCodeRepository;
use App\Repository\UserInformationRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractWebTest;

/**
 * RegisterTest
 */
class RegisterTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if user is registered
     * @return void
     */
    public function test_registerCorrect(): void
    {
        $userInformationRepository = $this->getService(UserInformationRepository::class);
        $registerCodeRepository = $this->getService(RegisterCodeRepository::class);

        $this->assertInstanceOf(RegisterCodeRepository::class, $registerCodeRepository);
        $this->assertInstanceOf(UserInformationRepository::class, $userInformationRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");
        /// step 2

        $content = [
            "email"=>"mosinskidamian11@gmail.com",
            "phoneNumber"=>"786768564",
            "firstname"=>"Damian",
            "lastname"=>"Mos",
            "password"=>"zaq12wsx"
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("PUT", "/api/register", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));
        /// step 4
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        /// step 5
        $userAfter = $userInformationRepository->findOneBy([
            "email"=>"mosinskidamian11@gmail.com"
        ])->getUser();

        $this->assertNotNull($userAfter);

        $hasRole = false;

        foreach ($userAfter->getRoles() as $role)
        {
            if($role->getName() == "Guest"){
                $hasRole = true;
            }
        }

        $this->assertTrue($hasRole);
        $this->assertFalse($userAfter->isActive());
        $this->assertCount(1, $registerCodeRepository->findAll());
    }
    /**
     * step 1 - Preparing JsonBodyContent with bad email
     * step 2 - Sending Request
     * step 3 - Checking response
     * @return void
     */
    public function test_registerIncorrectCredentials(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");
        /// step 1
        $content = [
            "email"=>"test@cos.pl",
            "phoneNumber"=>"786768564",
            "firstname"=>"Damian",
            "lastname"=>"Mos",
            "password"=>"zaq12wsx"
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $crawler = self::$webClient->request("PUT", "/api/register", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        $this->assertResponseStatusCodeSame(404);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey("error", $responseContent);
        $this->assertArrayHasKey("data", $responseContent);
    }
    /**
     * step 1 - Preparing JsonBodyContent with bad institution max users
     * step 2 - Sending Request
     * step 3 - Checking response
     * @return void
     */
    public function test_registerIncorrectInstitutionCredentials(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@1cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@2cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@3cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");
        /// step 1

        $content = [
            "email"=>"test2@cos.pl",
            "phoneNumber"=>"786768564",
            "firstname"=>"Damian",
            "lastname"=>"Mos",
            "password"=>"zaq12wsx"
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $crawler = self::$webClient->request("PUT", "/api/register", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        $this->assertResponseStatusCodeSame(404);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey("error", $responseContent);
        $this->assertArrayHasKey("data", $responseContent);
    }
    /**
     * step 1 - Sending Request without content
     * step 2 - Checking response
     * @return void
     */
    public function test_registerEmptyRequest()
    {
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        /// step 1
        $crawler = self::$webClient->request("PUT", "/api/register", server: [
            "HTTP_authorization" => $token->getToken()
        ]);
        /// step 2
        $this->assertResponseStatusCodeSame(400);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);
    }
}