<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminUserController;

use App\Repository\NotificationRepository;
use App\Repository\UserDeleteRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractWebTest;
use DateTime;

/**
 * AdminUserDeleteDeclineTest
 */
class AdminUserDeleteDeclineTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if user flags changed
     * @return void
     */
    public function test_adminUserDeleteAcceptCorrect(): void
    {
        $userRepository = $this->getService(UserRepository::class);
        $userDeleteRepository = $this->getService(UserDeleteRepository::class);
        $notificationRepository = $this->getService(NotificationRepository::class);

        $this->assertInstanceOf(NotificationRepository::class, $notificationRepository);
        $this->assertInstanceOf(UserDeleteRepository::class, $userDeleteRepository);
        $this->assertInstanceOf(UserRepository::class, $userRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $userDelete = $this->databaseMockManager->testFunc_addUserDelete($user2);

        /// step 2
        $content = [
            'userId' => $user2->getId()
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('PATCH', '/api/admin/user/delete/decline', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        /// step 5
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
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with user that is not in deleteUserList
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminUserDeleteAcceptIncorrectUserDeleted(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        /// step 2
        $content = [
            'userId' => $user2->getId()
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('PATCH', '/api/admin/user/delete/decline', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 4
        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with user that is deleted
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminUserDeleteAcceptIncorrectUser(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123186', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $this->databaseMockManager->testFunc_addUserDelete($user2, true, dateDeleted: new DateTime());

        /// step 2
        $content = [
            'userId' => $user2->getId()
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('PATCH', '/api/admin/user/delete/decline', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminUserDeleteAcceptEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        /// step 2
        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('PATCH', '/api/admin/user/delete/decline', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad permission
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminUserDeleteAcceptPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123128', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [
            'userId' => $user->getId()
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        self::$webClient->request('PATCH', '/api/admin/user/delete/decline', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without token
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminUserDeleteAcceptLogOut(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123125', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $content = [
            'userId' => $user->getId()
        ];

        /// step 2
        self::$webClient->request('PATCH', '/api/admin/user/delete/decline', content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
