<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminTechnicalController;

use App\Tests\AbstractWebTest;

class AdminTechnicalCachePoolsTest extends AbstractWebTest
{
    public function testAdminTechnicalCachePoolsCorrect(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('GET', '/api/admin/technical/cache/pools', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = $this->webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('userCachePools', $responseContent);
        $this->assertArrayHasKey('userCachePools', $responseContent);

        $this->assertCount(9, $responseContent['userCachePools']);
        $this->assertCount(6, $responseContent['adminCachePools']);
    }

    public function testAdminTechnicalCachePoolsPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('GET', '/api/admin/technical/cache/pools', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAdminTechnicalCachePoolsLogOut(): void
    {
        $this->webClient->request('GET', '/api/admin/technical/cache/pools');

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
