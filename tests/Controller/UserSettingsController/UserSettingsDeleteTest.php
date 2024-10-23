<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserSettingsController;

use App\Repository\UserDeleteRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractWebTest;

class UserSettingsDeleteTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response all data changed correctly
     * @return void
     */
    public function test_userSettingsDeleteCorrect(): void
    {
        $userRepository = $this->getService(UserRepository::class);
        $userDeleteRepository = $this->getService(UserDeleteRepository::class);

        $this->assertInstanceOf(UserDeleteRepository::class, $userDeleteRepository);
        $this->assertInstanceOf(UserRepository::class, $userRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('PATCH', '/api/user/settings/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $userAfter = $userRepository->findOneBy([
            'id' => $user->getId()
        ]);

        $this->assertFalse($userAfter->isActive());
        $this->assertCount(1, $userDeleteRepository->findAll());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request where user is in userDelete
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userSettingsDeleteIncorrectUser(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $this->databaseMockManager->testFunc_addUserDelete($user, true);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('PATCH', '/api/user/settings/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseStatusCodeSame(409);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function test_userSettingsDeletePermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('PATCH', '/api/user/settings/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_userSettingsDeleteLogOut(): void
    {
        self::$webClient->request('PATCH', '/api/user/settings/delete');

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
