<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserSettingsController;

use App\Repository\UserParentalControlCodeRepository;
use App\Tests\AbstractWebTest;

class UserParentControlPutTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response if all data has changed
     * @return void
     */
    public function test_userParentControlPutCorrect(): void
    {
        $userParentalControlCodeRepository = $this->getService(UserParentalControlCodeRepository::class);

        $this->assertInstanceOf(UserParentalControlCodeRepository::class, $userParentalControlCodeRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        self::$webClient->request('PUT', '/api/user/parent/control', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        /// step 3
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5
        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('code', $responseContent);

        $this->assertCount(1, $userParentalControlCodeRepository->findAll());
    }

    /**
     * /**
     *  step 1 - Preparing data
     *  step 2 - Preparing Data with bad AmountOfAttempts
     *  step 3 - Sending Request
     *  step 4 - Checking response
     *
     * @return void
     */
    public function test_userSettingsIncorrectAmountOfAttempts(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123121', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        /// step 2
        $this->databaseMockManager->testFunc_addUserParentalControlCode($user, false);
        $this->databaseMockManager->testFunc_addUserParentalControlCode($user, false);
        $this->databaseMockManager->testFunc_addUserParentalControlCode($user, false);
        $this->databaseMockManager->testFunc_addUserParentalControlCode($user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('PUT', '/api/user/parent/control', server: [
            'HTTP_authorization' => $token->getToken()
        ]);
        /// step 4
        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function test_userParentControlPutPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        self::$webClient->request('PUT', '/api/user/parent/control', server: [
            'HTTP_authorization' => $token->getToken()
        ]);
        /// step 3
        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_userParentControlPutLogOut(): void
    {
        /// step 2
        self::$webClient->request('PUT', '/api/user/parent/control');
        /// step 3
        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
