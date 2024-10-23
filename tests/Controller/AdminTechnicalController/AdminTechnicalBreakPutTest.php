<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminTechnicalController;

use App\Repository\TechnicalBreakRepository;
use App\Tests\AbstractWebTest;

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

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('PUT', '/api/admin/technical/break', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

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

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('PUT', '/api/admin/technical/break', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $this->assertCount(1, $technicalBreakRepository->findAll());
    }

    public function test_adminTechnicalBreakPutPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('PUT', '/api/admin/technical/break', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_adminTechnicalBreakPutLogOut(): void
    {
        self::$webClient->request('PUT', '/api/admin/technical/break');

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
