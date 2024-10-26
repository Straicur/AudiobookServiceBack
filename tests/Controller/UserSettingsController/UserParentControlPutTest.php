<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserSettingsController;

use App\Repository\UserParentalControlCodeRepository;
use App\Tests\AbstractWebTest;

class UserParentControlPutTest extends AbstractWebTest
{
    public function testUserParentControlPutCorrect(): void
    {
        $userParentalControlCodeRepository = $this->getService(UserParentalControlCodeRepository::class);

        $this->assertInstanceOf(UserParentalControlCodeRepository::class, $userParentalControlCodeRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('PUT', '/api/user/parent/control', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('code', $responseContent);

        $this->assertCount(1, $userParentalControlCodeRepository->findAll());
    }

    /**
     * Test checks if user don't break a max attempts limit
     */
    public function testUserSettingsIncorrectAmountOfAttempts(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123121', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');

        $this->databaseMockManager->testFunc_addUserParentalControlCode($user, false);
        $this->databaseMockManager->testFunc_addUserParentalControlCode($user, false);
        $this->databaseMockManager->testFunc_addUserParentalControlCode($user, false);
        $this->databaseMockManager->testFunc_addUserParentalControlCode($user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('PUT', '/api/user/parent/control', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function testUserParentControlPutPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('PUT', '/api/user/parent/control', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function testUserParentControlPutLogOut(): void
    {
        self::$webClient->request('PUT', '/api/user/parent/control');

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
