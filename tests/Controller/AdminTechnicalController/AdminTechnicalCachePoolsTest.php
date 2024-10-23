<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminTechnicalController;

use App\Tests\AbstractWebTest;

class AdminTechnicalCachePoolsTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if category is active
     * @return void
     */
    public function test_adminTechnicalCachePoolsCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('GET', '/api/admin/technical/cache/pools', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('userCachePools', $responseContent);
        $this->assertArrayHasKey('userCachePools', $responseContent);

        $this->assertCount(9, $responseContent['userCachePools']);
        $this->assertCount(6, $responseContent['adminCachePools']);
    }

    public function test_adminTechnicalCachePoolsPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        /// step 2
        self::$webClient->request('GET', '/api/admin/technical/cache/pools', server: [
            'HTTP_authorization' => $token->getToken()
        ]);
        /// step 3
        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_adminTechnicalCachePoolsLogOut(): void
    {
        /// step 2
        self::$webClient->request('GET', '/api/admin/technical/cache/pools');

        /// step 3
        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
