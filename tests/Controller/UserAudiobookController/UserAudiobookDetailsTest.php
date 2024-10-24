<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserAudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Tests\AbstractWebTest;
use DateTime;

class UserAudiobookDetailsTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response has returned correct data
     * @return void
     */
    public function testUserAudiobookDetailsCorrect(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd2', [$category2]);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category2]);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => $category1->getCategoryKey()
        ];

        self::$webClient->request('POST', '/api/user/audiobook/details', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('id', $responseContent);
        $this->assertArrayHasKey('title', $responseContent);
        $this->assertArrayHasKey('author', $responseContent);
        $this->assertArrayHasKey('version', $responseContent);
        $this->assertArrayHasKey('album', $responseContent);
        $this->assertArrayHasKey('year', $responseContent);
        $this->assertArrayHasKey('duration', $responseContent);
        $this->assertArrayHasKey('parts', $responseContent);
        $this->assertArrayHasKey('description', $responseContent);
        $this->assertArrayHasKey('age', $responseContent);
        $this->assertArrayHasKey('categories', $responseContent);
        $this->assertCount(2, $responseContent['categories']);
        $this->assertArrayHasKey('inList', $responseContent);
        $this->assertArrayHasKey('comments', $responseContent);
        $this->assertArrayHasKey('canRate', $responseContent);
        $this->assertArrayHasKey('canComment', $responseContent);
        $this->assertArrayHasKey('avgRating', $responseContent);
        $this->assertArrayHasKey('ratingAmount', $responseContent);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad CategoryKey
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function testUserAudiobookDetailsIncorrectCategoryKey(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd2', [$category2]);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category2]);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b'
        ];

        self::$webClient->request('POST', '/api/user/audiobook/details', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad AudiobookId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function testUserAudiobookDetailsIncorrectAudiobookId(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1,
            $category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd2', [$category2]);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category2]);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'audiobookId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
            'categoryKey' => $category1->getCategoryKey()
        ];

        self::$webClient->request('POST', '/api/user/audiobook/details', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function testUserAudiobookDetailsEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [];

        self::$webClient->request('POST', '/api/user/audiobook/details', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function testUserAudiobookDetailsPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => $category1->getCategoryKey()
        ];

        self::$webClient->request('POST', '/api/user/audiobook/details', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function testUserAudiobookDetailsLogOut(): void
    {
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => $category1->getCategoryKey()
        ];

        self::$webClient->request('POST', '/api/user/audiobook/details', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
