<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserReportController;

use App\Enums\AudiobookAgeRange;
use App\Enums\ReportType;
use App\Repository\ReportRepository;
use App\Tests\AbstractWebTest;
use DateTime;

class UserReportTest extends AbstractWebTest
{
    public function testUserReportCorrect(): void
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
            'additionalData' => [
                'description' => 'DESC',
                'actionId' => $comment1->getId(),
            ]
        ];

        $this->webClient->request('PUT', '/api/user/report', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $response = $this->webClient->getResponse();

        json_decode($response->getContent(), true);

        $this->assertCount(1, $reportRepository->findAll());
    }

    /**
     * Test checks if user don't break a max reports limit
     */
    public function testUserReportToManyReports(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);

        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1', user: $user, actionId: (string)$comment1->getId());
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1', user: $user, actionId: (string)$comment1->getId());
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1', user: $user, actionId: (string)$comment1->getId());
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1', user: $user, actionId: (string)$comment1->getId());
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1', user: $user, actionId: (string)$comment1->getId());
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1', user: $user, actionId: (string)$comment1->getId());
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1', user: $user, actionId: (string)$comment1->getId());

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'type' => ReportType::COMMENT->value,
            'additionalData' => [
                'description' => 'DESC',
                'actionId' => (string)$comment1->getId(),
            ]
        ];

        $this->webClient->request('PUT', '/api/user/report', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    public function testUserReportEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'additionalData' => [
                'description' => 'DESC',
            ]];

        $this->webClient->request('PUT', '/api/user/report', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testUserReportPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'type' => ReportType::COMMENT->value,
            'additionalData' => [
                'description' => 'DESC',
                'actionId' => $comment1->getId(),
            ]
        ];

        $this->webClient->request('PUT', '/api/user/report', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testUserReportLogOut(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);

        $content = [
            'type' => ReportType::COMMENT->value,
            'additionalData' => [
                'description' => 'DESC',
                'actionId' => $comment1->getId(),
            ]
        ];

        $this->webClient->request('PUT', '/api/user/report', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
