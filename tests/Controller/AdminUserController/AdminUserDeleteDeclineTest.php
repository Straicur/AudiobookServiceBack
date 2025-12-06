<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminUserController;

use App\Repository\NotificationRepository;
use App\Repository\UserDeleteRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractWebTest;
use DateTime;

class AdminUserDeleteDeclineTest extends AbstractWebTest
{
    public function testAdminUserDeleteAcceptCorrect(): void
    {
        $userRepository = $this->getService(UserRepository::class);
        $userDeleteRepository = $this->getService(UserDeleteRepository::class);
        $notificationRepository = $this->getService(NotificationRepository::class);

        $this->assertInstanceOf(NotificationRepository::class, $notificationRepository);
        $this->assertInstanceOf(UserDeleteRepository::class, $userDeleteRepository);
        $this->assertInstanceOf(UserRepository::class, $userRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $userDelete = $this->databaseMockManager->testFunc_addUserDelete($user2);

        $content = [
            'userId' => $user2->getId()
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/admin/user/delete/decline', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $userDeleteAfter = $userDeleteRepository->findOneBy([
            'id' => $userDelete->getId()
        ]);

        $this->assertTrue($userDeleteAfter->getDeclined());

        $userAfter = $userRepository->findOneBy([
            'id' => $user->getId()
        ]);

        $this->assertTrue($userAfter->isActive());

        $this->assertCount(1, $notificationRepository->findAll());
    }

    /**
     * Test checks bad given user(he is not in deleted users)
     */
    public function testAdminUserDeleteAcceptIncorrectUserDeleted(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $content = [
            'userId' => $user2->getId()
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/admin/user/delete/decline', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    /**
     * Test checks bad given user(he is deleted)
     */
    public function testAdminUserDeleteAcceptIncorrectUser(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123186', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $this->databaseMockManager->testFunc_addUserDelete($user2, true, dateDeleted: new DateTime());

        $content = [
            'userId' => $user2->getId()
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/admin/user/delete/decline', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    public function testAdminUserDeleteAcceptEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/admin/user/delete/decline', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAdminUserDeleteAcceptPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123128', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [
            'userId' => $user->getId()
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/admin/user/delete/decline', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAdminUserDeleteAcceptLogOut(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123125', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $content = [
            'userId' => $user->getId()
        ];

        $this->webClient->request('PATCH', '/api/admin/user/delete/decline', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
