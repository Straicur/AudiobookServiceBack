<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminReportController;

use App\Enums\AudiobookAgeRange;
use App\Enums\ReportType;
use App\Repository\ReportRepository;
use App\Tests\AbstractWebTest;
use DateTime;

/**
 * adminReportRejectTest
 */
class AdminReportRejectTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response
     * @return void
     */
    public function test_adminReportRejectCorrect(): void
    {
        $reportRepository = $this->getService(ReportRepository::class);

        $this->assertInstanceOf(ReportRepository::class, $reportRepository);

        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $report = $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1');

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $content = [
            'reportId' => $report->getId(),
            'answer'       => 'dsa',
            'rejectOthers' => false,
        ];
        /// step 2
        self::$webClient->request('PATCH', '/api/admin/report/reject', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        /// step 5
        $deniedAfter = $reportRepository->findOneBy([
            'id' => $report->getId()
        ]);
        $this->assertNotNull($deniedAfter);
        $this->assertTrue($deniedAfter->getDenied());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response
     * @return void
     */
    public function test_adminReportRejectOtherCorrect(): void
    {
        $reportRepository = $this->getService(ReportRepository::class);

        $this->assertInstanceOf(ReportRepository::class, $reportRepository);

        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1,
            $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $report = $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1', actionId: (string)$comment1->getId());
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1', actionId: (string)$comment1->getId());
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1', actionId: (string)$comment1->getId());

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $content = [
            'reportId'     => $report->getId(),
            'answer'       => 'dsa',
            'rejectOthers' => true,
        ];
        /// step 2
        self::$webClient->request('PATCH', '/api/admin/report/reject', server: [
            'HTTP_authorization' => $token->getToken(),
        ], content: json_encode($content));

        /// step 3
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        /// step 5
        $deniedAfter = $reportRepository->findOneBy([
            'id' => $report->getId(),
        ]);
        $this->assertNotNull($deniedAfter);
        $this->assertTrue($deniedAfter->getDenied());

        $deniedAfter2 = $reportRepository->findOneBy([
            'id' => $report->getId(),
        ]);
        $this->assertNotNull($deniedAfter2);
        $this->assertTrue($deniedAfter2->getDenied());

        $deniedAfter3 = $reportRepository->findOneBy([
            'id' => $report->getId(),
        ]);
        $this->assertNotNull($deniedAfter3);
        $this->assertTrue($deniedAfter3->getDenied());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad reportId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminReportRejectIncorrectReportId(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $content = [
            'reportId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
            'answer'       => 'dsa',
            'rejectOthers' => false,
        ];

        /// step 2
        self::$webClient->request('PATCH', '/api/admin/report/reject', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminReportRejectEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $content = [];

        /// step 2
        self::$webClient->request('PATCH', '/api/admin/report/reject', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad permission
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminReportRejectPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $report = $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, accepted: true, ip: '127.0.0.1');

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $content = [
            'reportId' => $report->getId(),
            'answer'       => 'dsa',
            'rejectOthers' => false,
        ];
        /// step 2
        self::$webClient->request('PATCH', '/api/admin/report/reject', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

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
    public function test_adminReportRejectLogOut(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $report = $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, accepted: true, ip: '127.0.0.1');

        /// step 2
        $content = [
            'reportId' => $report->getId(),
            'answer'       => 'dsa',
            'rejectOthers' => false,
        ];
        /// step 2
        self::$webClient->request('PATCH', '/api/admin/report/reject', content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
