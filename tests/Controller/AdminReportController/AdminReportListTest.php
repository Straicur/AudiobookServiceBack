<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminReportController;

use App\Enums\AudiobookAgeRange;
use App\Enums\ReportType;
use App\Tests\AbstractWebTest;
use DateTime;

/**
 * adminReportListTest
 */
class AdminReportListTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response
     * @return void
     */
    public function test_adminReportListNoFilterCorrect(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user3 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123128', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user1);

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
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, user: $user3);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, user: $user3);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, user: $user3);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, dateAdd: (new DateTime())->modify('-1 day'), user: $user3);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, dateAdd: (new DateTime())->modify('-2 day'), user: $user3);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, dateAdd: (new DateTime())->modify('-3 day'), user: $user3);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, dateAdd: (new DateTime())->modify('-1 day'), user: $user2);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, dateAdd: (new DateTime())->modify('-2 day'), user: $user2);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, dateAdd: (new DateTime())->modify('-3 day'), user: $user2);

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 2
        $content = [
            'page' => 0,
            'limit' => 10,
            'searchData' => []
        ];
        /// step 2
        self::$webClient->request('POST', '/api/admin/report/list', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5
        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('reports', $responseContent);
        $this->assertArrayHasKey('page', $responseContent);
        $this->assertArrayHasKey('limit', $responseContent);
        $this->assertArrayHasKey('maxPage', $responseContent);
        $this->assertCount(10, $responseContent['reports']);
        $this->assertSame(0, $responseContent['page']);
        $this->assertSame(10, $responseContent['limit']);
        $this->assertSame(3, $responseContent['maxPage']);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response
     * @return void
     */
    public function test_adminReportListCommentsCorrect(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123128', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1,
            $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user2);
        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment2', $audiobook1, $user2, $comment1);
        $comment3 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment3', $audiobook1, $user1, $comment1);

        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, dateAdd: (new DateTime())->modify('-1 day'), ip: '198.0.0.1', actionId: (string)$comment3->getId());

        $token = $this->databaseMockManager->testFunc_loginUser($user1);
        /// step 2
        $content = [
            'page'       => 0,
            'limit'      => 10,
            'searchData' => [],
        ];
        /// step 2
        self::$webClient->request('POST', '/api/admin/report/list',            server : [
            'HTTP_authorization' => $token->getToken(),
        ],                                                                     content: json_encode($content));

        /// step 3
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5
        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('reports', $responseContent);
        $this->assertArrayHasKey('page', $responseContent);
        $this->assertArrayHasKey('limit', $responseContent);
        $this->assertArrayHasKey('maxPage', $responseContent);
        $this->assertCount(1, $responseContent['reports']);
        $this->assertArrayHasKey('comment', $responseContent['reports'][0]);
        $this->assertArrayHasKey('children', $responseContent['reports'][0]['comment']);
        $this->assertCount(2, $responseContent['reports'][0]['comment']['children']);
        $this->assertSame(0, $responseContent['page']);
        $this->assertSame(10, $responseContent['limit']);
        $this->assertSame(1, $responseContent['maxPage']);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response
     * @return void
     */
    public function test_adminReportListSpecificCorrect(): void
    {
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user3 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123128', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user1);

        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1');
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1');
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1');
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '198.0.0.1');
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '198.0.0.1');
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '198.0.0.1');
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '198.0.0.1', dateAdd: (new DateTime())->modify('-1 day'));
        $this->databaseMockManager->testFunc_addReport(ReportType::AUDIOBOOK_PROBLEM, ip: '198.0.0.1', dateAdd: (new DateTime())->modify('-1 day'));
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '198.0.0.1', dateAdd: (new DateTime())->modify('-2 day'));
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '198.0.0.1', dateAdd: (new DateTime())->modify('-3 day'));
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1', dateAdd: (new DateTime())->modify('-1 day'));
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1', dateAdd: (new DateTime())->modify('-2 day'));
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, ip: '127.0.0.1', dateAdd: (new DateTime())->modify('-3 day'));
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, user: $user2);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, user: $user2);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, user: $user2);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, user: $user3);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, user: $user3);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, user: $user3);
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, user: $user3, dateAdd: (new DateTime())->modify('-1 day'));
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, user: $user3, dateAdd: (new DateTime())->modify('-2 day'));
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, user: $user3, dateAdd: (new DateTime())->modify('-3 day'));
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, user: $user2, dateAdd: (new DateTime())->modify('-1 day'));
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, user: $user2, dateAdd: (new DateTime())->modify('-2 day'));
        $this->databaseMockManager->testFunc_addReport(ReportType::COMMENT, user: $user2, dateAdd: (new DateTime())->modify('-3 day'));

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $dateFrom = new DateTime();
        $dateTo = new DateTime();

        /// step 2
        $content = [
            'page' => 0,
            'limit' => 10,
            'searchData' => [
                'ip' => '198.0.0.1',
                'dateFrom' => $dateFrom->modify('-2 day')->format('d.m.Y'),
                'dateTo' => $dateTo->format('d.m.Y'),
                'type' => 2
            ]
        ];
        /// step 2
        self::$webClient->request('POST', '/api/admin/report/list', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5
        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('reports', $responseContent);
        $this->assertArrayHasKey('page', $responseContent);
        $this->assertArrayHasKey('limit', $responseContent);
        $this->assertArrayHasKey('maxPage', $responseContent);
        $this->assertCount(1, $responseContent['reports']);
        $this->assertSame(0, $responseContent['page']);
        $this->assertSame(10, $responseContent['limit']);
        $this->assertSame(1, $responseContent['maxPage']);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminReportListEmptyRequestData(): void
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
            'page' => 0
        ];

        /// step 2
        self::$webClient->request('POST', '/api/admin/report/list', server: [
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
    public function test_adminReportListPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $content = [
            'page' => 0,
            'limit' => 10,
            'additionalData' => []
        ];
        /// step 2
        self::$webClient->request('POST', '/api/admin/report/list', server: [
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
    public function test_adminReportListLogOut(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);

        /// step 2
        $content = [
            'page' => 0,
            'limit' => 10,
            'additionalData' => []
        ];
        /// step 2
        self::$webClient->request('POST', '/api/admin/report/list', content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}