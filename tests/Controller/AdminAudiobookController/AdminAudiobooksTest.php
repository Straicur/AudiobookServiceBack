<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminAudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Tests\AbstractWebTest;
use DateTime;

class AdminAudiobooksTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response has returned correct data
     * @return void
     */
    public function test_adminAudiobooksCorrect(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t1', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], null, (new DateTime())->modify('- 1 month'));
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook('t2', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd2', [$category2], rating: 4);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook('t3', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category2], null, (new DateTime())->modify('- 1 year'));
        $this->databaseMockManager->testFunc_addAudiobook('t4', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd4', [$category1,
            $category2], null, (new DateTime())->modify('- 1 month'), rating: 4);
        $this->databaseMockManager->testFunc_addAudiobook('t5', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd5', [$category2]);
        $this->databaseMockManager->testFunc_addAudiobook('t6', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd6', [$category2], null, (new DateTime())->modify('- 1 year'), rating: 2);
        $audiobook7 = $this->databaseMockManager->testFunc_addAudiobook('t7', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd7', [$category1, $category2], null, (new DateTime())->modify('- 1 month'), rating: 2);
        $this->databaseMockManager->testFunc_addAudiobook('t8', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd8', [$category2], rating: 3);
        $this->databaseMockManager->testFunc_addAudiobook('t9', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd9', [$category2], null, (new DateTime())->modify('- 1 year'), rating: 3);
        $audiobook10 = $this->databaseMockManager->testFunc_addAudiobook('t10', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd10', [$category1, $category2], null, (new DateTime())->modify('- 1 month'), rating: 4);
        $this->databaseMockManager->testFunc_addAudiobook('t11', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd11', [$category2], rating: 4);
        $this->databaseMockManager->testFunc_addAudiobook('t12', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd12', [$category2], null, (new DateTime())->modify('- 1 year'), rating: 4);

        $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook2, 1, 21);
        $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook3, 1, 21);
        $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook10, 1, 21);
        $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook7, 1, 21);
        $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook1, 1, 21);

        $content = [
            'page' => 0,
            'limit' => 10,
            'searchData' => [
                'categories' => [
                    $category1->getId()
                ],
                'author' => 'a',
                'title' => 't',
                'album' => 'd',
                'duration' => 1,
                'parts' => 1,
                'age' => 5,
                'order' => 8,
                'year' => (new DateTime())->format('d.m.Y')
            ]
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('POST', '/api/admin/audiobooks', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);


        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('audiobooks', $responseContent);
        $this->assertCount(4, $responseContent['audiobooks']);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContentz
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response has returned correct data
     * @return void
     */
    public function test_adminAudiobooksNoFilterCorrect(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1,
            $category2], null, (new DateTime())->modify('- 1 month'));
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd2', [$category2]);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category2], null, (new DateTime())->modify('- 1 year'));
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd4', [$category1,
            $category2], null, (new DateTime())->modify('- 1 month'));
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd5', [$category2]);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd6', [$category2], null, (new DateTime())->modify('- 1 year'));
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd7', [$category1,
            $category2], null, (new DateTime())->modify('- 1 month'));
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd8', [$category2]);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd9', [$category2], null, (new DateTime())->modify('- 1 year'));
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd10', [$category1,
            $category2], null, (new DateTime())->modify('- 1 month'));
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd11', [$category2]);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd12', [$category2], null, (new DateTime())->modify('- 1 year'));

        $content = [
            'page' => 1,
            'limit' => 10,
            'searchData' => [
            ]
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('POST', '/api/admin/audiobooks', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);


        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('audiobooks', $responseContent);
        $this->assertCount(2, $responseContent['audiobooks']);
    }

    public function test_adminAudiobooksEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1,
            $category2]);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd2', [$category2]);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category2]);

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('POST', '/api/admin/audiobooks', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_adminAudiobooksPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1,
            $category2]);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd2', [$category2]);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category2]);

        $content = [
            'page' => 0,
            'limit' => 10,
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('POST', '/api/admin/audiobooks', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_adminAudiobooksLogOut(): void
    {
        $content = [
            'page' => 0,
            'limit' => 10,
        ];

        self::$webClient->request('POST', '/api/admin/audiobooks', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}