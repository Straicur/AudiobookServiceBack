<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminTechnicalController;

use App\Repository\TechnicalBreakRepository;
use App\Tests\AbstractWebTest;

class AdminTechnicalBreakPatchTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if technicalBreak changed
     * @return void
     */
    public function test_adminTechnicalBreakPatchCorrect(): void
    {
        $technicalBreakRepository = $this->getService(TechnicalBreakRepository::class);

        $this->assertInstanceOf(TechnicalBreakRepository::class, $technicalBreakRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $technicalBreak = $this->databaseMockManager->testFunc_addTechnicalBreak(true, $user);

        $content = [
            'technicalBreakId' => $technicalBreak->getId()
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('PATCH', '/api/admin/technical/break', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $technicalBreakAfter = $technicalBreakRepository->findOneBy([
            'id' => $technicalBreak->getId()
        ]);

        $this->assertFalse($technicalBreakAfter->getActive());
        $this->assertNotNull($technicalBreakAfter->getDateTo());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad audiobookId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminTechnicalBreakPatchIncorrectTechnicalBreakId(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $this->databaseMockManager->testFunc_addTechnicalBreak(true, $user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'technicalBreakId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b'
        ];

        self::$webClient->request('PATCH', '/api/admin/technical/break', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function test_adminTechnicalBreakPatchEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $this->databaseMockManager->testFunc_addTechnicalBreak(true, $user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [];

        self::$webClient->request('PATCH', '/api/admin/technical/break', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_adminTechnicalBreakPatchPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $technicalBreak = $this->databaseMockManager->testFunc_addTechnicalBreak(true, $user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'technicalBreakId' => $technicalBreak->getId()
        ];

        self::$webClient->request('PATCH', '/api/admin/technical/break', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_adminTechnicalBreakPatchLogOut(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $technicalBreak = $this->databaseMockManager->testFunc_addTechnicalBreak(true, $user);

        $content = [
            'technicalBreakId' => $technicalBreak->getId()
        ];

        self::$webClient->request('PATCH', '/api/admin/technical/break', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
