<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminTechnicalController;

use App\Tests\AbstractWebTest;
use DateTime;

/**
 * AdminTechnicalBreakListTest
 */
class AdminTechnicalBreakListTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if category is active
     * @return void
     */
    public function test_adminTechnicalBreakListCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123125', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $this->databaseMockManager->testFunc_addTechnicalBreak(true, $user);
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user, (new DateTime())->modify('-2 day'), (new DateTime())->modify('-1 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user2, (new DateTime())->modify('-3 day'), (new DateTime())->modify('-2 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user2, (new DateTime())->modify('-4 day'), (new DateTime())->modify('-3 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user, (new DateTime())->modify('-6 day'), (new DateTime())->modify('-5 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user, (new DateTime())->modify('-12 day'), (new DateTime())->modify('-11 day'));
        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $user, (new DateTime())->modify('-13 day'), (new DateTime())->modify('-12 day'));

        /// step 2
        $dateFrom = new DateTime();
        $dateTo = clone $dateFrom;

        $content = [
            'page' => 0,
            'limit' => 10,
            'searchData' => [
                'userId' => $user->getId(),
                'order' => 1,
                'dateFrom' => $dateFrom->modify('-6 day')->format('d.m.Y'),
                'dateTo' => $dateTo->modify('+2 day')->format('d.m.Y')
            ]
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request('POST', '/api/admin/technical/break/list', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('technicalBreaks', $responseContent);
        $this->assertArrayHasKey('page', $responseContent);
        $this->assertArrayHasKey('limit', $responseContent);
        $this->assertArrayHasKey('maxPage', $responseContent);
        $this->assertCount(2, $responseContent['technicalBreaks']);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminTechnicalBreakListEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $technicalBreak = $this->databaseMockManager->testFunc_addTechnicalBreak(true, $user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [];

        /// step 2
        $crawler = self::$webClient->request('POST', '/api/admin/technical/break/list', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(400);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('error', $responseContent);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad permission
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminTechnicalBreakListPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $technicalBreak = $this->databaseMockManager->testFunc_addTechnicalBreak(true, $user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $dateFrom = new DateTime();
        $dateTo = clone $dateFrom;

        $content = [
            'page' => 0,
            'limit' => 10,
            'searchData' => [
                'userId' => $user->getId(),
                'active' => true,
                'order' => 1,
                'dateFrom' => $dateFrom->modify('-2 day')->format('d.m.Y'),
                'dateTo' => $dateTo->modify('+2 day')->format('d.m.Y')
            ]
        ];

        /// step 2
        $crawler = self::$webClient->request('POST', '/api/admin/technical/break/list', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(403);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('error', $responseContent);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without token
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminTechnicalBreakListLogOut(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $technicalBreak = $this->databaseMockManager->testFunc_addTechnicalBreak(true, $user);

        /// step 2
        $dateFrom = new DateTime();
        $dateTo = clone $dateFrom;

        $content = [
            'page' => 0,
            'limit' => 10,
            'searchData' => [
                'userId' => $user->getId(),
                'active' => true,
                'order' => 1,
                'dateFrom' => $dateFrom->modify('-2 day')->format('d.m.Y'),
                'dateTo' => $dateTo->modify('+2 day')->format('d.m.Y')
            ]
        ];

        /// step 2
        $crawler = self::$webClient->request('POST', '/api/admin/technical/break/list', content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(401);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('error', $responseContent);
    }
}