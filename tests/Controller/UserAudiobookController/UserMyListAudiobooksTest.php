<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserAudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Tests\AbstractWebTest;
use DateTime;

class UserMyListAudiobooksTest extends AbstractWebTest
{
    public function testUserMyListAudiobookCorrect(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category2, true);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd2', [$category2], active: true);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category2], active: true);

        $audiobook4 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd4', [$category4, $category2], active: true);
        $audiobook5 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd5', [$category5], active: true);
        $audiobook6 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd6', [$category5], active: true);

        $this->databaseMockManager->testFunc_addMyList($user, $audiobook1);
        $this->databaseMockManager->testFunc_addMyList($user, $audiobook2);
        $this->databaseMockManager->testFunc_addMyList($user, $audiobook3);
        $this->databaseMockManager->testFunc_addMyList($user, $audiobook4);
        $this->databaseMockManager->testFunc_addMyList($user, $audiobook5);
        $this->databaseMockManager->testFunc_addMyList($user, $audiobook6);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('GET', '/api/user/myList/audiobooks', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('audiobooks', $responseContent);
        $this->assertCount(6, $responseContent['audiobooks']);

        $this->assertArrayHasKey('id', $responseContent['audiobooks'][0]);
        $this->assertArrayHasKey('title', $responseContent['audiobooks'][0]);
        $this->assertArrayHasKey('author', $responseContent['audiobooks'][0]);
        $this->assertArrayHasKey('parts', $responseContent['audiobooks'][0]);
        $this->assertArrayHasKey('age', $responseContent['audiobooks'][0]);
        $this->assertArrayHasKey('categories', $responseContent['audiobooks'][0]);
        $this->assertCount(2, $responseContent['audiobooks'][0]['categories']);
    }

    public function testUserMyListAudiobookPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('GET', '/api/user/myList/audiobooks', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function testUserMyListAudiobookLogOut(): void
    {
        self::$webClient->request('GET', '/api/user/myList/audiobooks');

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
