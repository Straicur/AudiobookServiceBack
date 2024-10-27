<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserAudiobookCommentController;

use App\Enums\AudiobookAgeRange;
use App\Repository\AudiobookUserCommentLikeRepository;
use App\Tests\AbstractWebTest;
use DateTime;

class UserAudiobookCommentLikeAddTest extends AbstractWebTest
{
    public function testUserAudiobookCommentLikeCorrect(): void
    {
        $audiobookUserCommentLikeRepository = $this->getService(AudiobookUserCommentLikeRepository::class);

        $this->assertInstanceOf(AudiobookUserCommentLikeRepository::class, $audiobookUserCommentLikeRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $comment2 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment2', $audiobook1, $user2);

        $this->databaseMockManager->testFunc_addAudiobookUserCommentLike(true, $comment2, $user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'commentId' => $comment2->getId(),
            'like' => true
        ];

        self::$webClient->request('PATCH', '/api/user/audiobook/comment/like/add', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $this->assertCount(1, $audiobookUserCommentLikeRepository->findAll());
    }

    /**
     * Test checks bad given commentId
     */
    public function testUserAudiobookCommentLikeIncorrectAudiobookCommentId(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'commentId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
            'like' => true
        ];

        self::$webClient->request('PATCH', '/api/user/audiobook/comment/like/add', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function testUserAudiobookCommentLikeEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [];

        self::$webClient->request('PATCH', '/api/user/audiobook/comment/like/add', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function testUserAudiobookCommentLikePermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $comment2 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user2);

        $this->databaseMockManager->testFunc_addAudiobookUserCommentLike(true, $comment2, $user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'commentId' => $comment2->getId(),
            'like' => true
        ];

        self::$webClient->request('PATCH', '/api/user/audiobook/comment/like/add', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function testUserAudiobookCommentLikeLogOut(): void
    {
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $comment2 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user2);

        $content = [
            'commentId' => $comment2->getId(),
            'like' => true
        ];

        self::$webClient->request('PATCH', '/api/user/audiobook/comment/like/add', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
