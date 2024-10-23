<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserAudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Tests\AbstractWebTest;
use DateTime;

class UserAudiobooksTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response has returned correct data
     * @return void
     */
    public function test_userAudiobooksCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category2, true);

        $this->databaseMockManager->testFunc_addAudiobook('t1', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1,
            $category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t2', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd2', [$category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t3', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t4', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd4', [$category1,
            $category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t5', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd5', [$category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t6', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd6', [$category5]);
        $this->databaseMockManager->testFunc_addAudiobook('t7', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd7', [$category1,
            $category4], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t8', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd8', [$category4]);
        $this->databaseMockManager->testFunc_addAudiobook('t9', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd9', [$category4], active: true);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'page' => 0,
            'limit' => 10
        ];

        /// step 2
        self::$webClient->request('POST', '/api/user/audiobooks', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5
        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('page', $responseContent);
        $this->assertArrayHasKey('limit', $responseContent);
        $this->assertArrayHasKey('maxPage', $responseContent);
        $this->assertArrayHasKey('categories', $responseContent);
        $this->assertCount(3, $responseContent['categories']);
        $this->assertCount(5, $responseContent['categories'][0]['audiobooks']);
        $this->assertArrayHasKey('name', $responseContent['categories'][0]);
        $this->assertArrayHasKey('categoryKey', $responseContent['categories'][0]);
        $this->assertSame($category2->getCategoryKey(), $responseContent['categories'][0]['categoryKey']);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response has returned correct data
     * @return void
     */
    public function test_userAudiobooksParentControlCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', birthday: (new DateTime())->modify('-14 year'));

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category2, true);

        $this->databaseMockManager->testFunc_addAudiobook('t1', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::FROM3TO7, 'd1', [$category1,
            $category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t2', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::FROM3TO7, 'd2', [$category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t3', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::FROM7TO12, 'd3', [$category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t4', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd4', [$category1,
            $category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t5', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::FROM12TO16, 'd5', [$category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t6', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::FROM12TO16, 'd6', [$category5]);
        $this->databaseMockManager->testFunc_addAudiobook('t7', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::FROM16TO18, 'd7', [$category1,
            $category4], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t8', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::FROM16TO18, 'd8', [$category4]);
        $this->databaseMockManager->testFunc_addAudiobook('t9', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd9', [$category4], active: true);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'page' => 0,
            'limit' => 10
        ];

        /// step 2
        self::$webClient->request('POST', '/api/user/audiobooks', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5
        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('page', $responseContent);
        $this->assertArrayHasKey('limit', $responseContent);
        $this->assertArrayHasKey('maxPage', $responseContent);
        $this->assertArrayHasKey('categories', $responseContent);
        $this->assertCount(2, $responseContent['categories']);
        $this->assertCount(4, $responseContent['categories'][0]['audiobooks']);
        $this->assertArrayHasKey('name', $responseContent['categories'][0]);
        $this->assertArrayHasKey('categoryKey', $responseContent['categories'][0]);
        $this->assertSame($category2->getCategoryKey(), $responseContent['categories'][0]['categoryKey']);
    }

    public function test_userAudiobooksEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [];

        /// step 2
        self::$webClient->request('POST', '/api/user/audiobooks', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));


        /// step 3
        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_userAudiobooksPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        $content = [
            'page' => 0,
            'limit' => 10
        ];

        /// step 2
        self::$webClient->request('POST', '/api/user/audiobooks', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_userAudiobooksLogOut(): void
    {
        /// step 1
        $content = [
            'page' => 0,
            'limit' => 10
        ];

        /// step 2
        self::$webClient->request('POST', '/api/user/audiobooks', content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
