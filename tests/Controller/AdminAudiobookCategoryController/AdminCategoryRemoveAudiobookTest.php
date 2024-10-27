<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminAudiobookCategoryController;

use App\Enums\AudiobookAgeRange;
use App\Repository\AudiobookRepository;
use App\Tests\AbstractWebTest;
use DateTime;

class AdminCategoryRemoveAudiobookTest extends AbstractWebTest
{
    public function testAdminCategoryRemoveAudiobookCorrect(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);

        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd', [$category1, $category2]);

        $content = [
            'categoryId' => $category1->getId(),
            'audiobookId' => $audiobook->getId(),
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('DELETE', '/api/admin/category/remove/audiobook', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $audiobookAfter = $audiobookRepository->findOneBy([
            'id' => $audiobook->getId()
        ]);

        $categories = $audiobookAfter->getCategories();

        $hasCategory = false;

        foreach ($categories as $category) {
            if ($category->getId() === $category1->getId()) {
                $hasCategory = true;
            }
        }

        $this->assertFalse($hasCategory);
    }

    /**
     * Test checks bad given categoryId
     */
    public function testAdminCategoryRemoveAudiobookIncorrectCategoryId(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd', [$category1, $category2]);

        $content = [
            'categoryId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
            'audiobookId' => $audiobook->getId(),
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('DELETE', '/api/admin/category/remove/audiobook', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * Test checks bad given audiobookId
     */
    public function testAdminCategoryRemoveAudiobookIncorrectAudiobookId(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd', [$category1,
            $category2]);

        $content = [
            'categoryId' => $category2->getId(),
            'audiobookId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('DELETE', '/api/admin/category/remove/audiobook', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function testAdminCategoryRemoveAudiobookEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd', [$category1, $category2]);

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('DELETE', '/api/admin/category/remove/audiobook', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function testAdminCategoryRemoveAudiobookPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd', [$category1, $category2]);

        $content = [
            'categoryId' => $category2->getId(),
            'audiobookId' => $audiobook->getId(),
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('DELETE', '/api/admin/category/remove/audiobook', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function testAdminCategoryRemoveAudiobookLogOut(): void
    {
        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd', [$category1, $category2]);

        $content = [
            'categoryId' => $category2->getId(),
            'audiobookId' => $audiobook->getId(),
        ];

        self::$webClient->request('DELETE', '/api/admin/category/remove/audiobook', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
