<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminTechnicalController;

use App\Repository\TechnicalBreakRepository;
use App\Tests\AbstractWebTest;

class AdminTechnicalBreakPatchTest extends AbstractWebTest
{
    public function testAdminTechnicalBreakPatchCorrect(): void
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
     * Test checks bad given technicalBreakId
     */
    public function testAdminTechnicalBreakPatchIncorrectTechnicalBreakId(): void
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

    public function testAdminTechnicalBreakPatchEmptyRequestData(): void
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

    public function testAdminTechnicalBreakPatchPermission(): void
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

    public function testAdminTechnicalBreakPatchLogOut(): void
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
