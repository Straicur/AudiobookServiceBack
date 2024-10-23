<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminUserController;

use App\Repository\UserDeleteRepository;
use App\Tests\AbstractWebTest;
use DateTime;

class AdminUserDeleteAcceptTest extends AbstractWebTest
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
        $userDeleteRepository = $this->getService(UserDeleteRepository::class);

        $this->assertInstanceOf(UserDeleteRepository::class, $userDeleteRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $userDelete = $this->databaseMockManager->testFunc_addUserDelete($user2);

        $content = [
            'userId' => $user2->getId()
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('PATCH', '/api/admin/user/delete/accept', server : [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $userDeleteAfter = $userDeleteRepository->findOneBy([
            'id' => $userDelete->getId()
        ]);

        $this->assertTrue($userDeleteAfter->getDeleted());
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
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $content = [
            'userId' => $user2->getId()
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('PATCH', '/api/admin/user/delete/accept', server : [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

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
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $this->databaseMockManager->testFunc_addUserDelete($user2, true, dateDeleted: new DateTime());

        $content = [
            'userId' => $user2->getId()
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('PATCH', '/api/admin/user/delete/accept', server : [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function test_adminUserDeleteAcceptEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('PATCH', '/api/admin/user/delete/accept', server : [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_adminUserDeleteAcceptPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [
            'userId' => $user->getId()
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('PATCH', '/api/admin/user/delete/accept', server : [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_adminUserDeleteAcceptLogOut(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $content = [
            'userId' => $user->getId()
        ];

        self::$webClient->request('PATCH', '/api/admin/user/delete/accept', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
