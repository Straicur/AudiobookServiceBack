<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminAudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Tests\AbstractWebTest;
use DateTime;

class AdminAudiobookCommentGetTest extends AbstractWebTest
{
    public function testAdminAudiobookChangeCoverCorrect(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123120', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123129', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

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
            'audiobookId' => $audiobook1->getId()
        ];

        $this->webClient->request('POST', '/api/admin/audiobook/comment/get', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = $this->webClient->getResponse();

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

    public function testAdminAudiobookChangeCoverEmptyRequestData(): void
    {

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123125', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment2', $audiobook1, $user1);
        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment3', $audiobook1, $user2);
        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment3', $audiobook1, $user2, $comment1);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [];

        $this->webClient->request('POST', '/api/user/audiobook/details', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAdminAudiobookChangeCoverPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123124', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123122', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment2', $audiobook1, $user1);
        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment3', $audiobook1, $user2);
        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment3', $audiobook1, $user2, $comment1);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'audiobookId' => $audiobook1->getId()
        ];

        $this->webClient->request('POST', '/api/admin/audiobook/comment/get', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAdminAudiobookChangeCoverLogOut(): void
    {
        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $content = [
            'audiobookId' => $audiobook1->getId()
        ];

        $this->webClient->request('POST', '/api/admin/audiobook/comment/get', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
