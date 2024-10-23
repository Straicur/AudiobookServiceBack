<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminUserController;

use App\Enums\UserRoles;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractWebTest;

class AdminUserRoleRemoveTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if role is deleted
     * @return void
     */
    public function test_adminUserRoleRemoveCorrect(): void
    {
        $userRepository = $this->getService(UserRepository::class);
        $roleRepository = $this->getService(RoleRepository::class);

        $this->assertInstanceOf(RoleRepository::class, $roleRepository);
        $this->assertInstanceOf(UserRepository::class, $userRepository);
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123128', ['Guest', 'User'], true, 'zaq12wsx', notActive: true);
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest', 'User'], true, 'zaq12wsx');

        /// step 2
        $content = [
            'userId' => $user2->getId(),
            'role' => UserRoles::USER
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 3
        self::$webClient->request('PATCH', '/api/admin/user/role/remove', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
        /// step 5
        $userRole = $roleRepository->findOneBy([
            'name' => 'User'
        ]);
        $this->assertFalse($userRepository->userHasRole($user2, $userRole));
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad Admin user
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminUserRoleRemoveIncorrectAdminUser(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123128', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest', 'User'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 2
        $content = [
            'userId' => $user1->getId(),
            'role' => UserRoles::USER->value
        ];

        /// step 3
        self::$webClient->request('PATCH', '/api/admin/user/role/remove', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 4
        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad userId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminUserRoleRemoveIncorrectUserId(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', notActive: true);
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123128', ['Guest', 'User'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        /// step 2
        $content = [
            'userId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
            'role' => UserRoles::USER->value
        ];

        /// step 3
        self::$webClient->request('PATCH', '/api/admin/user/role/remove', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 4
        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function test_adminUserRoleRemoveEmptyRequestData(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', notActive: true);
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123129', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123128', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 2
        self::$webClient->request('PATCH', '/api/admin/user/role/remove', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_adminUserRoleRemovePermission(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx', notActive: true);
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123128', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [
            'userId' => $user2->getId(),
            'role' => UserRoles::USER->value
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 2
        self::$webClient->request('PATCH', '/api/admin/user/role/remove', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_adminUserRoleRemoveLogOut(): void
    {
        /// step 1
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123129', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $content = [
            'userId' => $user2->getId(),
            'role' => UserRoles::USER->value
        ];
        /// step 2
        self::$webClient->request('PATCH', '/api/admin/user/role/remove', content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
