<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserAudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Repository\AudiobookUserCommentRepository;
use App\Tests\AbstractWebTest;
use DateTime;

/**
 * UserAudiobookCommentEditTest
 */
class UserAudiobookCommentEditTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response if comment is eddited
     * @return void
     */
    public function test_userAudiobookCommentEditCorrect(): void
    {
        $audiobookUserCommentRepository = $this->getService(AudiobookUserCommentRepository::class);

        $this->assertInstanceOf(AudiobookUserCommentRepository::class, $audiobookUserCommentRepository);

        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $comment2 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment2', $audiobook1, $user2);

        $token = $this->databaseMockManager->testFunc_loginUser($user2);
        /// step 2
        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => $category1->getCategoryKey(),
            'audiobookCommentId'=>$comment2->getId(),
            'comment' => 'comment',
            'deleted'=>false,
        ];
        /// step 2
        $crawler = self::$webClient->request('PATCH', '/api/user/audiobook/comment/edit', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5

        $commentAfter = $audiobookUserCommentRepository->findOneBy([
            'id'=>$comment2->getId()
        ]);

        $this->assertSame($content['comment'],$commentAfter->getComment());
        $this->assertSame($content['deleted'],$commentAfter->getDeleted());
        $this->assertTrue($commentAfter->getEdited());

    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad CategoryKey
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_userAudiobookCommentEditIncorrectCategoryKey(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $comment2 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user2);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
            'audiobookCommentId'=>$comment2->getId(),
            'comment' => 'comment',
            'deleted'=>false,
            'additionalData' => [
                'parentId' => $comment1->getId()
            ]
        ];
        /// step 2
        $crawler = self::$webClient->request('PATCH', '/api/user/audiobook/comment/edit', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseStatusCodeSame(404);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('error', $responseContent);
        $this->assertArrayHasKey('data', $responseContent);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad AudiobookId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_userAudiobookCommentEditIncorrectAudiobookId(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $comment2 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user2);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
            'audiobookCommentId'=>$comment2->getId(),
            'comment' => 'comment',
            'deleted'=>false,
            'additionalData' => [
                'parentId' => $comment1->getId()
            ]
        ];

        /// step 2
        $crawler = self::$webClient->request('PATCH', '/api/user/audiobook/comment/edit', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseStatusCodeSame(404);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('error', $responseContent);
        $this->assertArrayHasKey('data', $responseContent);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad ParentId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_userAudiobookCommentEditIncorrectParentId(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $comment2 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user2);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => $category1->getCategoryKey(),
            'audiobookCommentId'=>$comment2->getId(),
            'comment' => 'comment',
            'deleted'=>false,
            'additionalData' => [
                'parentId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b'
            ]
        ];

        /// step 2
        $crawler = self::$webClient->request('PATCH', '/api/user/audiobook/comment/edit', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseStatusCodeSame(404);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('error', $responseContent);
        $this->assertArrayHasKey('data', $responseContent);
    }
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad ParentId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_userAudiobookCommentEditIncorrectAudiobookCommentId(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $comment2 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user2);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => $category1->getCategoryKey(),
            'audiobookCommentId'=>'66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
            'comment' => 'comment',
            'deleted'=>false,
            'additionalData' => [
            ]
        ];

        /// step 2
        $crawler = self::$webClient->request('PATCH', '/api/user/audiobook/comment/edit', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseStatusCodeSame(404);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('error', $responseContent);
        $this->assertArrayHasKey('data', $responseContent);
    }
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userAudiobookCommentEditEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $content = [];

        /// step 2
        $crawler = self::$webClient->request('PATCH', '/api/user/audiobook/comment/edit', server: [
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
    public function test_userAudiobookCommentEditPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $comment2 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user2);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => $category1->getCategoryKey(),
            'audiobookCommentId'=>$comment2->getId(),
            'comment' => 'comment',
            'deleted'=>false,
            'additionalData' => [
                'parentId' => $comment1->getId()
            ]
        ];
        /// step 2
        $crawler = self::$webClient->request('PATCH', '/api/user/audiobook/comment/edit', server: [
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
    public function test_userAudiobookCommentEditLogOut(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $comment2 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user2);

        /// step 2
        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => $category1->getCategoryKey(),
            'audiobookCommentId'=>$comment2->getId(),
            'comment' => 'comment',
            'deleted'=>false,
            'additionalData' => [
                'parentId' => $comment1->getId()
            ]
        ];
        /// step 2
        $crawler = self::$webClient->request('PATCH', '/api/user/audiobook/comment/edit', content: json_encode($content));

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