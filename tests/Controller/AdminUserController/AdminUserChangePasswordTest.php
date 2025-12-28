<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminUserController;

use App\Repository\UserPasswordRepository;
use App\Tests\AbstractWebTest;
use App\ValueGenerator\PasswordHashGenerator;

class AdminUserChangePasswordTest extends AbstractWebTest
{
    public function testAdminUserDetailsCorrect(): void
    {
        $userPasswordRepository = $this->getService(UserPasswordRepository::class);

        $this->assertInstanceOf(UserPasswordRepository::class, $userPasswordRepository);

        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest', 'User'], true, 'zaq12wsX', notActive: true);
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123128', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [
            'userId' => $user2->getId(),
            'newPassword' => 'zaq12wsx'
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $this->webClient->request('PATCH', '/api/admin/user/change/password', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $passwordGenerator = new PasswordHashGenerator($content['newPassword']);

        $user2PasswordAfter = $userPasswordRepository->findOneBy([
            'user' => $user2->getId()
        ]);

        $this->assertSame($passwordGenerator->generate(), $user2PasswordAfter->getPassword());
    }

    /**
     * Test checks bad given user(he is an admin)
     */
    public function testAdminUserDetailsIncorrectAdminUser(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123128', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123129', ['Guest', 'User'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $content = [
            'userId' => $user1->getId(),
            'newPassword' => 'zaq12wsx'
        ];

        $this->webClient->request('PATCH', '/api/admin/user/change/password', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    /**
     * Test checks bad given userId
     */
    public function testAdminUserDetailsIncorrectUserId(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', notActive: true);
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123129', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123126', ['Guest', 'User'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $content = [
            'userId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
            'newPassword' => 'zaq12wsx'
        ];

        $this->webClient->request('PATCH', '/api/admin/user/change/password', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    public function testAdminUserDetailsEmptyRequestData(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', notActive: true);
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123128', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $this->webClient->request('PATCH', '/api/admin/user/change/password', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAdminUserDetailsPermission(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx', notActive: true);
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123129', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123124', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [
            'userId' => $user2->getId(),
            'newPassword' => 'zaq12wsx'
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $this->webClient->request('PATCH', '/api/admin/user/change/password', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAdminUserDetailsLogOut(): void
    {
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx', notActive: true);
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123125', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123126', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [
            'userId' => $user2->getId(),
            'newPassword' => 'zaq12wsx'
        ];

        $this->webClient->request('PATCH', '/api/admin/user/change/password', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
