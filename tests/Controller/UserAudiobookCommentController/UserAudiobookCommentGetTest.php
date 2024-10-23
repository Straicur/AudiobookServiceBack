<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserAudiobookCommentController;

use App\Enums\AudiobookAgeRange;
use App\Tests\AbstractWebTest;
use DateTime;

class UserAudiobookCommentGetTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response has returned correct data
     * @return void
     */
    public function test_audiobookCommentGetCorrect(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $comment2 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment2', $audiobook1, $user1);
        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment3', $audiobook1, $user2);
        $comment4 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment4', $audiobook1, $user2, $comment1);

        $this->databaseMockManager->testFunc_addAudiobookUserCommentLike(true, $comment1, $user1);
        $this->databaseMockManager->testFunc_addAudiobookUserCommentLike(false, $comment1, $user2);
        $this->databaseMockManager->testFunc_addAudiobookUserCommentLike(false, $comment2, $user2, true);
        $this->databaseMockManager->testFunc_addAudiobookUserCommentLike(true, $comment4, $user1);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => $category1->getCategoryKey()
        ];

        self::$webClient->request('POST', '/api/user/audiobook/comment/get', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('comments', $responseContent);
        $this->assertCount(3, $responseContent['comments']);
        $this->assertArrayHasKey('id', $responseContent['comments'][0]);
        $this->assertArrayHasKey('comment', $responseContent['comments'][0]);
        $this->assertArrayHasKey('edited', $responseContent['comments'][0]);
        $this->assertArrayHasKey('children', $responseContent['comments'][0]);
        $this->assertCount(1, $responseContent['comments'][0]['children']);
        $this->assertArrayHasKey('myComment', $responseContent['comments'][0]);
        $this->assertArrayHasKey('userModel', $responseContent['comments'][0]);
    }

    public function test_audiobookCommentGetEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment2', $audiobook1, $user1);
        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment3', $audiobook1, $user2);
        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment3', $audiobook1, $user2, $comment1);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [];

        self::$webClient->request('POST', '/api/user/audiobook/details', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_audiobookCommentGetPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment2', $audiobook1, $user1);
        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment3', $audiobook1, $user2);
        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment3', $audiobook1, $user2, $comment1);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => $category1->getCategoryKey()
        ];

        self::$webClient->request('POST', '/api/user/audiobook/comment/get', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_audiobookCommentGetLogOut(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment2', $audiobook1, $user1);
        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment3', $audiobook1, $user2);
        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment3', $audiobook1, $user2, $comment1);

        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => $category1->getCategoryKey()
        ];

        self::$webClient->request('POST', '/api/user/audiobook/comment/get', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
