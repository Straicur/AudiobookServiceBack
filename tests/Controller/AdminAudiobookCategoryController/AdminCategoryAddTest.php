<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminAudiobookCategoryController;

use App\Repository\AudiobookCategoryRepository;
use App\Tests\AbstractWebTest;

class AdminCategoryAddTest extends AbstractWebTest
{
    public function testAdminCategoryAddCorrect(): void
    {
        $audiobookCategoryRepository = $this->getService(AudiobookCategoryRepository::class);

        $this->assertInstanceOf(AudiobookCategoryRepository::class, $audiobookCategoryRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $content = [
            'name' => '3',
            'additionalData' => [
                'parentId' => $category2->getId()
            ]
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PUT', '/api/admin/category/add', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $this->assertCount(14, $audiobookCategoryRepository->findAll());
    }

    /**
     * Test checks bad given parentId category Id
     */
    public function testAdminCategoryAddIncorrectCategoryId(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $content = [
            'name' => '3',
            'additionalData' => [
                'parentId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b'
            ]
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PUT', '/api/admin/category/add', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    public function testAdminCategoryAddEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PUT', '/api/admin/category/add', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAdminCategoryAddPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $content = [
            'name' => '3',
            'additionalData' => [
                'parentId' => $category2->getId()
            ]
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PUT', '/api/admin/category/add', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAdminCategoryAddLogOut(): void
    {
        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $content = [
            'name' => '3',
            'additionalData' => [
                'parentId' => $category2->getId()
            ]
        ];

        $this->webClient->request('PUT', '/api/admin/category/add', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
