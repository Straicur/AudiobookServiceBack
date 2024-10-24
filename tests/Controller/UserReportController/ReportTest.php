<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserReportController;

use App\Enums\AudiobookAgeRange;
use App\Enums\ReportType;
use App\Repository\ReportRepository;
use App\Tests\AbstractWebTest;
use DateTime;

class ReportTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response
     * @return void
     */
    public function testReportCorrect(): void
    {
        $reportRepository = $this->getService(ReportRepository::class);

        $this->assertInstanceOf(ReportRepository::class, $reportRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'type' => ReportType::COMMENT->value,
            'ip' => '127.0.0.1',
            'email' => 'test@gmail.com',
            'additionalData' => [
                'description' => 'DESC',
                'actionId' => $comment1->getId(),
            ]
        ];

        self::$webClient->request('PUT', '/api/report', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $this->assertCount(1, $reportRepository->findAll());
    }
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function testReportToManyReports(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);

        $this->databaseMockManager->testFunc_addReport(ReportType::SYSTEM_PROBLEM, ip: '127.0.0.1');
        $this->databaseMockManager->testFunc_addReport(ReportType::SYSTEM_PROBLEM, ip: '127.0.0.1');
        $this->databaseMockManager->testFunc_addReport(ReportType::SYSTEM_PROBLEM, ip: '127.0.0.1');
        $this->databaseMockManager->testFunc_addReport(ReportType::SYSTEM_PROBLEM, ip: '127.0.0.1');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'type' => ReportType::SYSTEM_PROBLEM->value,
            'ip' => '127.0.0.1',
            'email' => 'test@gmail.com',
            'additionalData' => [
                'description' => 'DESC',
            ]
        ];

        self::$webClient->request('PUT', '/api/report', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function testReportEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'type' => ReportType::COMMENT->value,
            'ip' => '127.0.0.1',
        ];

        self::$webClient->request('PUT', '/api/report', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
