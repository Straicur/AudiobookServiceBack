<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminTechnicalController;

use App\Repository\TechnicalBreakRepository;
use App\Tests\AbstractWebTest;

/**
 * AdminTechnicalBreakPutTest
 */
class AdminTechnicalBreakPutTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if count of TechnicalBreak is correct
     * @return void
     */
    public function test_adminTechnicalBreakPutCorrect(): void
    {
        $technicalBreakRepository = $this->getService(TechnicalBreakRepository::class);

        $this->assertInstanceOf(TechnicalBreakRepository::class, $technicalBreakRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        /// step 2

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('PUT', '/api/admin/technical/break', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);
        /// step 5
        $this->assertCount(1, $technicalBreakRepository->findAll());
    }
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if count of TechnicalBreak is correct
     * @return void
     */
    public function test_adminTechnicalBreakPutOnlyOneExistsdCorrect(): void
    {
        $technicalBreakRepository = $this->getService(TechnicalBreakRepository::class);

        $this->assertInstanceOf(TechnicalBreakRepository::class, $technicalBreakRepository);
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addTechnicalBreak(true, $user);
        /// step 2
        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('PUT', '/api/admin/technical/break', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);
        /// step 5
        $this->assertCount(1, $technicalBreakRepository->findAll());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad permission
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminTechnicalBreakPutPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);


        /// step 2
        self::$webClient->request('PUT', '/api/admin/technical/break', server: [
            'HTTP_authorization' => $token->getToken()
        ]);
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
    public function test_adminTechnicalBreakPutLogOut(): void
    {
        /// step 2
        self::$webClient->request('PUT', '/api/admin/technical/break');

        /// step 3
        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
