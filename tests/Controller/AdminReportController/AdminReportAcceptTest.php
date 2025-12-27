<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminReportController;

use App\Enums\AudiobookAgeRange;
use App\Enums\ReportType;
use App\Repository\ReportRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractWebTest;
use DateTime;

class AdminReportAcceptTest extends AbstractWebTest
{
    public function testAdminAcceptCorrect(): void
    {
        $reportRepository = $this->getService(ReportRepository::class);

        $this->assertInstanceOf(ReportRepository::class, $reportRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $report = $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'reportId' => $report->getId(),
            'banPeriod' => 2,
            'acceptOthers' => false,
        ];

        $this->webClient->request('PATCH', '/api/admin/report/accept', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $acceptedAfter = $reportRepository->findOneBy([
            'id' => $report->getId()
        ]);
        $this->assertNotNull($acceptedAfter);
        $this->assertTrue($acceptedAfter->getAccepted());
    }

    /**
     * Test checks a correct accept with ban with accepting others reports
     */
    public function testAdminAcceptOthersCorrect(): void
    {
        $reportRepository = $this->getService(ReportRepository::class);

        $this->assertInstanceOf(ReportRepository::class, $reportRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1,
            $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $report = $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1', actionId: (string)$comment1->getId());
        $report2 = $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1', actionId: (string)$comment1->getId());
        $report3 = $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1', actionId: (string)$comment1->getId());

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'reportId'     => $report->getId(),
            'banPeriod'    => 2,
            'acceptOthers' => true,
        ];

        $this->webClient->request('PATCH', '/api/admin/report/accept', server: [
            'HTTP_authorization' => $token->getToken(),
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $acceptedAfter = $reportRepository->findOneBy([
            'id' => $report->getId(),
        ]);
        $this->assertNotNull($acceptedAfter);
        $this->assertTrue($acceptedAfter->getAccepted());

        $acceptedAfter2 = $reportRepository->findOneBy([
            'id' => $report2->getId(),
        ]);
        $this->assertNotNull($acceptedAfter2);
        $this->assertTrue($acceptedAfter2->getAccepted());

        $acceptedAfter3 = $reportRepository->findOneBy([
            'id' => $report3->getId(),
        ]);
        $this->assertNotNull($acceptedAfter3);
        $this->assertTrue($acceptedAfter3->getAccepted());
    }

    /**
     * Test checks a correct accept with ban
     */
    public function testAdminAcceptSystemBanCorrect(): void
    {
        $reportRepository = $this->getService(ReportRepository::class);
        $userRepository = $this->getService(UserRepository::class);

        $this->assertInstanceOf(UserRepository::class, $userRepository);
        $this->assertInstanceOf(ReportRepository::class, $reportRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $report = $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1', actionId: (string)$comment1->getId());
        $newDate = new DateTime();

        $this->databaseMockManager->testFunc_addUserBanHistory($user, (clone $newDate)->modify('-11 month'), (clone $newDate)->modify('-10 month'));

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'reportId' => $report->getId(),
            'banPeriod' => 1,
            'acceptOthers' => false,
        ];

        $this->webClient->request('PATCH', '/api/admin/report/accept', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $acceptedAfter = $reportRepository->findOneBy([
            'id' => $report->getId()
        ]);
        $this->assertNotNull($acceptedAfter);
        $this->assertTrue($acceptedAfter->getAccepted());

        $userAfter = $userRepository->findOneBy([
            'id' => $user->getId()
        ]);

        $this->assertTrue($userAfter->isBanned());
        $this->assertTrue($userAfter->getBannedTo() > $newDate);
    }

    /**
     * Test checks bad given reportId
     */
    public function testAdminAcceptIncorrectReportId(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'reportId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
            'banPeriod' => 1,
            'acceptOthers' => false,
        ];

        $this->webClient->request('PATCH', '/api/admin/report/accept', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    public function testAdminAcceptEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [];

        $this->webClient->request('PATCH', '/api/admin/report/accept', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAdminAcceptPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $report = $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, accepted: true, ip: '127.0.0.1');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'reportId'     => $report->getId(),
            'acceptOthers' => false,
        ];

        $this->webClient->request('PATCH', '/api/admin/report/accept', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAdminAcceptLogOut(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $report = $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, accepted: true, ip: '127.0.0.1');

        $content = [
            'reportId'     => $report->getId(),
            'acceptOthers' => false,
        ];

        $this->webClient->request('PATCH', '/api/admin/report/accept', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
