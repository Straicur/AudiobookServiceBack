<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminUserController;

use App\Repository\UserSettingsRepository;
use App\Tests\AbstractWebTest;

class AdminUsersTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response has returned correct data
     * @return void
     */
    public function test_adminUsersCorrect(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123129', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123128', ['Guest', 'User'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test4@cos.pl', '+48123123127', ['Guest', 'User'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test5@cos.pl', '+48123123126', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [
            'page' => 0,
            'limit' => 10,
            'searchData' => [
                'email' => 'test',
                'phoneNumber' => '812',
                'firstname' => 'Us',
                'lastname' => 'Te',
                'active' => true,
                'banned' => false,
                'order' => 1,
            ]
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        self::$webClient->request('POST', '/api/admin/users', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('users', $responseContent);
        $this->assertArrayHasKey('page', $responseContent);
        $this->assertArrayHasKey('limit', $responseContent);
        $this->assertArrayHasKey('maxPage', $responseContent);
        $this->assertCount(3, $responseContent['users']);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response has returned correct data
     * @return void
     */
    public function test_adminUsersSpecificSearchCorrect(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123128', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest', 'User'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test4@cos.pl', '+48123123126', ['Guest', 'User'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test5@cos.pl', '+48123123125', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [
            'page' => 0,
            'limit' => 10,
            'searchData' => [
                'email' => 'test4',
                'phoneNumber' => '812',
                'firstname' => 'Us',
                'lastname' => 'Te',
                'active' => true,
                'banned' => false,
                'order' => 1,
            ]
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        self::$webClient->request('POST', '/api/admin/users', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('users', $responseContent);
        $this->assertArrayHasKey('page', $responseContent);
        $this->assertArrayHasKey('limit', $responseContent);
        $this->assertArrayHasKey('maxPage', $responseContent);
        $this->assertCount(1, $responseContent['users']);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response has returned correct data
     * @return void
     */
    public function test_adminUsersNoFilterCorrect(): void
    {
        $userSettings = $this->getService(UserSettingsRepository::class);

        $this->assertInstanceOf(UserSettingsRepository::class, $userSettings);

        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123128', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest', 'User'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test4@cos.pl', '+48123123126', ['Guest', 'User'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test5@cos.pl', '+48123123125', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [
            'page' => 0,
            'limit' => 10,
            'searchData' => []
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        self::$webClient->request('POST', '/api/admin/users', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('users', $responseContent);
        $this->assertArrayHasKey('page', $responseContent);
        $this->assertArrayHasKey('limit', $responseContent);
        $this->assertArrayHasKey('maxPage', $responseContent);
        $this->assertCount(9, $responseContent['users']);
    }

    public function test_adminUsersEmptyRequestData(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', notActive: true);

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        self::$webClient->request('POST', '/api/admin/users', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_adminUsersPermission(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User',], true, 'zaq12wsx', notActive: true);

        $content = [
            'page' => 0,
            'limit' => 10,
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        self::$webClient->request('POST', '/api/admin/users', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_adminUsersLogOut(): void
    {
        $content = [
            'page' => 0,
            'limit' => 10,
        ];

        self::$webClient->request('POST', '/api/admin/users', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
