<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminAudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Repository\AudiobookUserCommentRepository;
use App\Tests\AbstractWebTest;
use DateTime;

class AdminAudiobookCommentDeleteTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if category is active
     * @return void
     */
    public function test_adminAudiobookCommentDeleteCorrect(): void
    {
        $audiobookCommentRepository = $this->getService(AudiobookUserCommentRepository::class);

        $this->assertInstanceOf(AudiobookUserCommentRepository::class, $audiobookCommentRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd', [$category1, $category2], null, (new DateTime())->modify('- 1 month'), active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);

        /// step 2
        $content = [
            'audiobookCommentId' => $comment1->getId()
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('PATCH', '/api/admin/audiobook/comment/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $commentAfter = $audiobookCommentRepository->findOneBy([
            'id' => $comment1->getId()
        ]);

        $this->assertTrue($commentAfter->getDeleted());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if category is active
     * @return void
     */
    public function test_adminAudiobookCommentRestoreCorrect(): void
    {
        $audiobookCommentRepository = $this->getService(AudiobookUserCommentRepository::class);

        $this->assertInstanceOf(AudiobookUserCommentRepository::class, $audiobookCommentRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd', [$category1, $category2], null, (new DateTime())->modify('- 1 month'), active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user, deleted: true);

        /// step 2
        $content = [
            'audiobookCommentId' => $comment1->getId()
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('PATCH', '/api/admin/audiobook/comment/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $commentAfter = $audiobookCommentRepository->findOneBy([
            'id' => $comment1->getId()
        ]);

        $this->assertFalse($commentAfter->getDeleted());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad audiobookId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminAudiobookCommentDeleteIncorrectCommentId(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        /// step 2
        $content = [
            'audiobookCommentId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b'
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        /// step 3
        self::$webClient->request('PATCH', '/api/admin/audiobook/comment/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function test_adminAudiobookCommentDeleteEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        /// step 2
        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        /// step 3
        self::$webClient->request('PATCH', '/api/admin/audiobook/comment/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_adminAudiobookCommentDeletePermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        /// step 2
        $content = [
            'audiobookCommentId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b'
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        /// step 3
        self::$webClient->request('PATCH', '/api/admin/audiobook/comment/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_adminAudiobookCommentDeleteLogOut(): void
    {
        $content = [
            'audiobookCommentId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b'
        ];

        /// step 3
        self::$webClient->request('PATCH', '/api/admin/audiobook/comment/delete', content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
