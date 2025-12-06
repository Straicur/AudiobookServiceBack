<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminUserController;

use App\Repository\UserRepository;
use App\Tests\AbstractWebTest;

class AdminUserBanTest extends AbstractWebTest
{
    public function testAdminUserBanCorrect(): void
    {
        $userRepository = $this->getService(UserRepository::class);

        $this->assertInstanceOf(UserRepository::class, $userRepository);

        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123125', ['Guest', 'User'], true, 'zaq12wsx', notActive: true);
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123124', ['Guest', 'User'], true, 'zaq12wsx');


        $content = [
            'userId' => $user2->getId(),
            'banned' => true
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $this->webClient->request('PATCH', '/api/admin/user/ban', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));


        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $user2After = $userRepository->findOneBy([
            'id' => $user2->getId()
        ]);

        $this->assertTrue($user2After->isBanned());
    }

    /**
     * Test checks bad given user(he is an admin)
     */
    public function testAdminUserBanIncorrectAdminUser(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123124', ['Guest', 'User'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $content = [
            'userId' => $user2->getId(),
            'banned' => true
        ];


        $this->webClient->request('PATCH', '/api/admin/user/ban', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    /**
     * Test checks bad given userId
     */
    public function testAdminUserBanIncorrectUserId(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', notActive: true);
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123120', ['Guest', 'User'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $content = [
            'userId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
            'banned' => true
        ];


        $this->webClient->request('PATCH', '/api/admin/user/ban', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    public function testAdminUserBanEmptyRequestData(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', notActive: true);
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123129', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123128', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $this->webClient->request('PATCH', '/api/admin/user/ban', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAdminUserBanPermission(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx', notActive: true);
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123126', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [
            'userId' => $user2->getId(),
            'banned' => true
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $this->webClient->request('PATCH', '/api/admin/user/ban', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));


        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAdminUserBanLogOut(): void
    {
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx', notActive: true);
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123126', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [
            'userId' => $user2->getId(),
            'banned' => true
        ];

        $this->webClient->request('PATCH', '/api/admin/user/ban', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
