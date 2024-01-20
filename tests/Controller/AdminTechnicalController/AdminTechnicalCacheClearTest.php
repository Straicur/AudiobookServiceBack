<?php

namespace App\Tests\Controller\AdminTechnicalController;

use App\Tests\AbstractWebTest;

/**
 * AdminTechnicalCacheClearTest
 */
class AdminTechnicalCacheClearTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if category is active
     * @return void
     */
    public function test_adminTechnicalCacheClearCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123125", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $this->databaseMockManager->testFunc_addTechnicalBreak(true, $user);
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user, (new \DateTime("Now"))->modify('-2 day'), (new \DateTime("Now"))->modify('-1 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user2, (new \DateTime("Now"))->modify('-3 day'), (new \DateTime("Now"))->modify('-2 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user2, (new \DateTime("Now"))->modify('-4 day'), (new \DateTime("Now"))->modify('-3 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user, (new \DateTime("Now"))->modify('-6 day'), (new \DateTime("Now"))->modify('-5 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user, (new \DateTime("Now"))->modify('-12 day'), (new \DateTime("Now"))->modify('-11 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user, (new \DateTime("Now"))->modify('-13 day'), (new \DateTime("Now"))->modify('-12 day'));

        /// step 2
        $dateFrom = new \DateTime("Now");
        $dateTo = clone $dateFrom;

        $content = [
            "cacheData" => [
                "pools" => ["AdminCategory", "AdminAudiobookComments", "UserAudiobooks", "UserAudiobookRating"],
                "admin" => false,
                "user" => false,
                "all" => false,
            ]
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/admin/technical/cache/clear", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if category is active
     * @return void
     */
    public function test_adminTechnicalCacheClearAdminCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123125", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $this->databaseMockManager->testFunc_addTechnicalBreak(true, $user);
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user, (new \DateTime("Now"))->modify('-2 day'), (new \DateTime("Now"))->modify('-1 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user2, (new \DateTime("Now"))->modify('-3 day'), (new \DateTime("Now"))->modify('-2 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user2, (new \DateTime("Now"))->modify('-4 day'), (new \DateTime("Now"))->modify('-3 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user, (new \DateTime("Now"))->modify('-6 day'), (new \DateTime("Now"))->modify('-5 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user, (new \DateTime("Now"))->modify('-12 day'), (new \DateTime("Now"))->modify('-11 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user, (new \DateTime("Now"))->modify('-13 day'), (new \DateTime("Now"))->modify('-12 day'));

        /// step 2
        $dateFrom = new \DateTime("Now");
        $dateTo = clone $dateFrom;

        $content = [
            "cacheData" => [
                "pools" => [],
                "admin" => false,
                "user" => false,
                "all" => false,
            ]
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/admin/technical/cache/clear", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if category is active
     * @return void
     */
    public function test_adminTechnicalCacheClearUserCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123125", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $this->databaseMockManager->testFunc_addTechnicalBreak(true, $user);
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user, (new \DateTime("Now"))->modify('-2 day'), (new \DateTime("Now"))->modify('-1 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user2, (new \DateTime("Now"))->modify('-3 day'), (new \DateTime("Now"))->modify('-2 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user2, (new \DateTime("Now"))->modify('-4 day'), (new \DateTime("Now"))->modify('-3 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user, (new \DateTime("Now"))->modify('-6 day'), (new \DateTime("Now"))->modify('-5 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user, (new \DateTime("Now"))->modify('-12 day'), (new \DateTime("Now"))->modify('-11 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user, (new \DateTime("Now"))->modify('-13 day'), (new \DateTime("Now"))->modify('-12 day'));

        /// step 2
        $dateFrom = new \DateTime("Now");
        $dateTo = clone $dateFrom;

        $content = [
            "cacheData" => [
                "pools" => [],
                "admin" => false,
                "user" => false,
                "all" => false,
            ]
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/admin/technical/cache/clear", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if category is active
     * @return void
     */
    public function test_adminTechnicalCacheClearAllCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123125", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        $this->databaseMockManager->testFunc_addTechnicalBreak(true, $user);
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user, (new \DateTime("Now"))->modify('-2 day'), (new \DateTime("Now"))->modify('-1 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user2, (new \DateTime("Now"))->modify('-3 day'), (new \DateTime("Now"))->modify('-2 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user2, (new \DateTime("Now"))->modify('-4 day'), (new \DateTime("Now"))->modify('-3 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user, (new \DateTime("Now"))->modify('-6 day'), (new \DateTime("Now"))->modify('-5 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user, (new \DateTime("Now"))->modify('-12 day'), (new \DateTime("Now"))->modify('-11 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user, (new \DateTime("Now"))->modify('-13 day'), (new \DateTime("Now"))->modify('-12 day'));

        /// step 2
        $dateFrom = new \DateTime("Now");
        $dateTo = clone $dateFrom;

        $content = [
            "cacheData" => [
                "pools" => [],
                "admin" => false,
                "user" => false,
                "all" => false,
            ]
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request("PATCH", "/api/admin/technical/cache/clear", server: [
            "HTTP_authorization" => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad permission
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminTechnicalCacheClearPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            "cacheData" => [
                "pools" => [],
                "admin" => false,
                "user" => false,
                "all" => false,
            ]
        ];

        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/admin/technical/cache/clear", server: [
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
    public function test_adminTechnicalCacheClearLogOut(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User", "Administrator"], true, "zaq12wsx");

        /// step 2
        $content = [
            "cacheData" => [
                "pools" => [],
                "admin" => false,
                "user" => false,
                "all" => false,
            ]
        ];

        /// step 2
        $crawler = self::$webClient->request("PATCH", "/api/admin/technical/cache/clear", content: json_encode($content));

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