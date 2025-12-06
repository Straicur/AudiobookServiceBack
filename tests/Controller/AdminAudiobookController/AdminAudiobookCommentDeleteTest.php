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
     * Test checks a correct delete comment
     */
    public function testAdminAudiobookCommentDeleteCorrect(): void
    {
        $audiobookCommentRepository = $this->getService(AudiobookUserCommentRepository::class);

        $this->assertInstanceOf(AudiobookUserCommentRepository::class, $audiobookCommentRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd', [$category1, $category2], null, (new DateTime())->modify('- 1 month'), active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);

        $content = [
            'audiobookCommentId' => $comment1->getId()
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/admin/audiobook/comment/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $commentAfter = $audiobookCommentRepository->findOneBy([
            'id' => $comment1->getId()
        ]);

        $this->assertTrue($commentAfter->getDeleted());
    }

    /**
     * Test checks a correct restore comment
     */
    public function testAdminAudiobookCommentDeleteRestoreCorrect(): void
    {
        $audiobookCommentRepository = $this->getService(AudiobookUserCommentRepository::class);

        $this->assertInstanceOf(AudiobookUserCommentRepository::class, $audiobookCommentRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd', [$category1, $category2], null, (new DateTime())->modify('- 1 month'), active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user, deleted: true);

        $content = [
            'audiobookCommentId' => $comment1->getId()
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/admin/audiobook/comment/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $commentAfter = $audiobookCommentRepository->findOneBy([
            'id' => $comment1->getId()
        ]);

        $this->assertFalse($commentAfter->getDeleted());
    }

    /**
     * Test checks bad given audiobookCommentId
     */
    public function testAdminAudiobookCommentDeleteIncorrectCommentId(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $content = [
            'audiobookCommentId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b'
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/admin/audiobook/comment/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    public function testAdminAudiobookCommentDeleteEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/admin/audiobook/comment/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAdminAudiobookCommentDeletePermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [
            'audiobookCommentId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b'
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/admin/audiobook/comment/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAdminAudiobookCommentDeleteLogOut(): void
    {
        $content = [
            'audiobookCommentId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b'
        ];

        $this->webClient->request('PATCH', '/api/admin/audiobook/comment/delete', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
