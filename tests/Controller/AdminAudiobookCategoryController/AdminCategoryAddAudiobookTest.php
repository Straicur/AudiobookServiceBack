<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminAudiobookCategoryController;

use App\Enums\AudiobookAgeRange;
use App\Repository\AudiobookRepository;
use App\Tests\AbstractWebTest;
use DateTime;

class AdminCategoryAddAudiobookTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if category is added to audiobook
     * @return void
     */
    public function test_adminCategoryAddAudiobookCorrect(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);

        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd', [$category1]);

        $content = [
            'categoryId' => $category2->getId(),
            'audiobookId' => $audiobook->getId(),
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('PUT', '/api/admin/category/add/audiobook', server : [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $audiobookAfter = $audiobookRepository->findOneBy([
            'id' => $audiobook->getId()
        ]);

        $categories = $audiobookAfter->getCategories();

        $this->assertCount(2, $categories);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad CategoryId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminCategoryAddAudiobookIncorrectCategoryId(): void
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

        self::$webClient->request('PUT', '/api/admin/category/add/audiobook', server : [
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
    public function test_adminCategoryAddAudiobookIncorrectAudiobookId(): void
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

        self::$webClient->request('PUT', '/api/admin/category/add/audiobook', server : [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function test_adminCategoryAddAudiobookEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd', [$category1,
            $category2]);

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('PUT', '/api/admin/category/add/audiobook', server : [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_adminCategoryAddAudiobookPermission(): void
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

        self::$webClient->request('PUT', '/api/admin/category/add/audiobook', server : [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_adminCategoryAddAudiobookLogOut(): void
    {
        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd', [$category1, $category2]);

        $content = [
            'categoryId' => $category2->getId(),
            'audiobookId' => $audiobook->getId(),
        ];

        self::$webClient->request('PUT', '/api/admin/category/add/audiobook', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
