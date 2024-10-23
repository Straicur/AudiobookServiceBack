<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminUserController;

use App\Tests\AbstractWebTest;

class AdminUserDeleteListTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if returned data is correct
     * @return void
     */
    public function test_adminUserDeleteListCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123128', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123127', ['Guest', 'User',], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User',], true, 'zaq12wsx');
        $user3 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123125', ['Guest', 'User',], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test4@cos.pl', '+48123123123', ['Guest', 'User',], true, 'zaq12wsx');

        $this->databaseMockManager->testFunc_addUserDelete($user1);
        $this->databaseMockManager->testFunc_addUserDelete($user2, true);
        $this->databaseMockManager->testFunc_addUserDelete($user3, false, true);

        /// step 2
        $content = [
            'page' => 0,
            'limit' => 10
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('POST', '/api/admin/user/delete/list', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5
        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('users', $responseContent);
        $this->assertArrayHasKey('page', $responseContent);
        $this->assertArrayHasKey('limit', $responseContent);
        $this->assertArrayHasKey('maxPage', $responseContent);
        $this->assertCount(1, $responseContent['users']);
    }

    public function test_adminUserDeleteListEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123128', ['Guest', 'User',], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest', 'User',], true, 'zaq12wsx');
        $user3 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123126', ['Guest', 'User',], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test4@cos.pl', '+48123123155', ['Guest', 'User',], true, 'zaq12wsx');

        $this->databaseMockManager->testFunc_addUserDelete($user1);
        $this->databaseMockManager->testFunc_addUserDelete($user2, true);
        $this->databaseMockManager->testFunc_addUserDelete($user3, false, true);

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        self::$webClient->request('POST', '/api/admin/user/delete/list', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_adminUserDeleteListPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123128', ['Guest', 'User',], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest', 'User',], true, 'zaq12wsx');
        $user3 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123126', ['Guest', 'User',], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test4@cos.pl', '+48123123125', ['Guest', 'User',], true, 'zaq12wsx');

        $this->databaseMockManager->testFunc_addUserDelete($user1);
        $this->databaseMockManager->testFunc_addUserDelete($user2, true);
        $this->databaseMockManager->testFunc_addUserDelete($user3, false, true);

        $content = [
            'page' => 0,
            'limit' => 10
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        self::$webClient->request('POST', '/api/admin/user/delete/list', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_adminUserDeleteListLogOut(): void
    {
        /// step 1
        $content = [
            'page' => 0,
            'limit' => 10
        ];

        /// step 2
        self::$webClient->request('POST', '/api/admin/user/delete/list', content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
