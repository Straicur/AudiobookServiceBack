<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserReportController;

use App\Enums\AudiobookAgeRange;
use App\Enums\ReportType;
use App\Tests\AbstractWebTest;
use DateTime;

class UserReportsTest extends AbstractWebTest
{
    public function testUserReportsCorrect(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $user3 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123128', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1,
            $category2], active: true);

        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1');
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1');
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1');
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '198.0.0.1');
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '198.0.0.1');
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '198.0.0.1');
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, dateAdd: (new DateTime())->modify('-1 day'), ip: '198.0.0.1');
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, dateAdd: (new DateTime())->modify('-2 day'), ip: '198.0.0.1');
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, dateAdd: (new DateTime())->modify('-3 day'), ip: '198.0.0.1');
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, dateAdd: (new DateTime())->modify('-1 day'), ip: '127.0.0.1');
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, dateAdd: (new DateTime())->modify('-2 day'), ip: '127.0.0.1');
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, dateAdd: (new DateTime())->modify('-3 day'), ip: '127.0.0.1');
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, user: $user2);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, user: $user2);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, user: $user2);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, user: $user1);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, user: $user1);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, user: $user1);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, dateAdd: (new DateTime())->modify('-1 day'), user: $user3);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, dateAdd: (new DateTime())->modify('-2 day'), user: $user1);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, dateAdd: (new DateTime())->modify('-3 day'), user: $user1);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, dateAdd: (new DateTime())->modify('-1 day'), user: $user1);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, dateAdd: (new DateTime())->modify('-2 day'), user: $user2);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, dateAdd: (new DateTime())->modify('-3 day'), user: $user2);

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $content = [
            'page'  => 0,
            'limit' => 10,
        ];

        self::$webClient->request('POST', '/api/user/reports', server: [
            'HTTP_authorization' => $token->getToken(),
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('reports', $responseContent);
        $this->assertArrayHasKey('page', $responseContent);
        $this->assertArrayHasKey('limit', $responseContent);
        $this->assertArrayHasKey('maxPage', $responseContent);
        $this->assertCount(6, $responseContent['reports']);
        $this->assertSame(0, $responseContent['page']);
        $this->assertSame(10, $responseContent['limit']);
        $this->assertSame(1, $responseContent['maxPage']);
    }

    public function testUserReportsEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'page' => 0,
        ];

        self::$webClient->request('POST', '/api/user/reports', server: [
            'HTTP_authorization' => $token->getToken(),
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function testUserReportsPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'page'  => 0,
            'limit' => 10,
        ];

        self::$webClient->request('POST', '/api/user/reports', server: [
            'HTTP_authorization' => $token->getToken(),
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function testUserReportsLogOut(): void
    {
        $content = [
            'page'  => 0,
            'limit' => 10,
        ];

        self::$webClient->request('POST', '/api/user/reports', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
