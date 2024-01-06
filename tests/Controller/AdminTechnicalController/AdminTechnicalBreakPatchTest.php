<?php

namespace App\Tests\Controller\AdminTechnicalController;

use App\Repository\TechnicalBreakRepository;
use App\Tests\AbstractWebTest;

/**
 * AdminTechnicalBreakPatchTest
 */
class AdminTechnicalBreakPatchTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if technicalBreak changed
     * @return void
     */
    public function test_adminTechnicalBreakPatchCorrect(): void
    {
        $technicalBreakRepository = $this->getService(TechnicalBreakRepository::class);

        $this->assertInstanceOf(TechnicalBreakRepository::class, $technicalBreakRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $technicalBreak = $this->databaseMockManager->testFunc_addTechnicalBreak(true, $user);

        /// step 2
        $content = [
            "technicalBreakId" => $technicalBreak->getId()
        ];
        
        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/admin/technical/break", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $technicalBreakAfter = $technicalBreakRepository->findOneBy([
            "id" => $technicalBreak->getId()
        ]);

        $this->assertFalse($technicalBreakAfter->getActive());
        $this->assertNotNull($technicalBreakAfter->getDateTo());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad audiobookId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminTechnicalBreakPatchIncorrectTechnicalBreakId(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $technicalBreak = $this->databaseMockManager->testFunc_addTechnicalBreak(true, $user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            "technicalBreakId" => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b'
        ];

        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/admin/technical/break", server: [
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
    public function test_adminTechnicalBreakPatchEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $technicalBreak = $this->databaseMockManager->testFunc_addTechnicalBreak(true, $user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [];

        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/admin/technical/break", server: [
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
    public function test_adminTechnicalBreakPatchPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");

        $technicalBreak = $this->databaseMockManager->testFunc_addTechnicalBreak(true, $user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            "technicalBreakId" => $technicalBreak->getId()
        ];

        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/admin/technical/break", server: [
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
    public function test_adminTechnicalBreakPatchLogOut(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $technicalBreak = $this->databaseMockManager->testFunc_addTechnicalBreak(true, $user);

        /// step 2
        $content = [
            "technicalBreakId" => $technicalBreak->getId()
        ];

        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/admin/technical/break", content: json_encode($content));

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