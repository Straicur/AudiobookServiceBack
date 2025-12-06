<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminUserController;

use App\Tests\AbstractWebTest;

class AdminUserSystemRolesTest extends AbstractWebTest
{
    public function testAdminUserSystemRolesCorrect(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $this->webClient->request('GET', '/api/admin/user/system/roles', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = $this->webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('roles', $responseContent);
        $this->assertCount(2, $responseContent['roles']);
    }

    public function testAdminUserSystemRolesPermission(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User',], true, 'zaq12wsx', notActive: true);

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $this->webClient->request('GET', '/api/admin/user/system/roles', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAdminUserSystemRolesLogOut(): void
    {
        $this->webClient->request('GET', '/api/admin/user/system/roles');

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
