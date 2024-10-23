<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserAudiobookCommentController;

use App\Enums\AudiobookAgeRange;
use App\Repository\AudiobookUserCommentLikeRepository;
use App\Tests\AbstractWebTest;
use DateTime;

class UserAudiobookCommentLikeDeleteTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response if comment is eddited
     * @return void
     */
    public function test_userAudiobookCommentLikeCorrect(): void
    {
        $audiobookUserCommentLikeRepository = $this->getService(AudiobookUserCommentLikeRepository::class);

        $this->assertInstanceOf(AudiobookUserCommentLikeRepository::class, $audiobookUserCommentLikeRepository);

        /// step 1
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

        $commentLike = $this->databaseMockManager->testFunc_addAudiobookUserCommentLike(true, $comment2, $user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2

        /// step 2
        $content = [
            'commentId' => $comment2->getId(),
        ];
        /// step 2
        self::$webClient->request('DELETE', '/api/user/audiobook/comment/like/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
        /// step 5

        $commentLikeAfter = $audiobookUserCommentLikeRepository->findOneBy([
            'id' => $commentLike->getId()
        ]);

        $this->assertTrue($commentLikeAfter->getDeleted());
    }

    public function test_userAudiobookCommentLikeIncorrectAudiobookCommentLike(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $comment2 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user2);

        $this->databaseMockManager->testFunc_addAudiobookUserCommentLike(true, $comment2, $user, true);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2

        /// step 2
        $content = [
            'commentId' => $comment2->getId(),
        ];

        /// step 2
        self::$webClient->request('DELETE', '/api/user/audiobook/comment/like/delete', server: [
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
    public function test_userAudiobookCommentLikeEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $content = [];

        /// step 2
        self::$webClient->request('DELETE', '/api/user/audiobook/comment/like/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_userAudiobookCommentLikePermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $comment2 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user2);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2

        /// step 2
        $content = [
            'commentId' => $comment2->getId(),
        ];
        /// step 2
        self::$webClient->request('DELETE', '/api/user/audiobook/comment/like/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_userAudiobookCommentLikeLogOut(): void
    {
        /// step 1
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $comment2 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user2);

        /// step 2

        /// step 2
        $content = [
            'commentId' => $comment2->getId(),
        ];
        /// step 2
        self::$webClient->request('DELETE', '/api/user/audiobook/comment/like/delete', content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
