<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminAudiobookCategoryController;

use App\Repository\AudiobookCategoryRepository;
use App\Tests\AbstractWebTest;

class AdminCategoryActiveTest extends AbstractWebTest
{
    /**
     * Test checks a correct activation
     */
    public function testAdminCategoryActiveCorrect(): void
    {
        $audiobookCategoryRepository = $this->getService(AudiobookCategoryRepository::class);

        $this->assertInstanceOf(AudiobookCategoryRepository::class, $audiobookCategoryRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $content = [
            'categoryId' => $category1->getId(),
            'active' => true
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/admin/category/active', server : [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $category1After = $audiobookCategoryRepository->findOneBy([
            'id' => $category1->getId()
        ]);

        $this->assertTrue($category1After->getActive());
    }

    /**
     * Test checks a correct deactivation
     */
    public function testAdminCategoryActiveDeactivateCorrect(): void
    {
        $audiobookCategoryRepository = $this->getService(AudiobookCategoryRepository::class);

        $this->assertInstanceOf(AudiobookCategoryRepository::class, $audiobookCategoryRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $content = [
            'categoryId' => $category1->getId(),
            'active' => false
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/admin/category/active', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $category1After = $audiobookCategoryRepository->findOneBy([
            'id' => $category1->getId()
        ]);

        $this->assertFalse($category1After->getActive());
    }

    /**
     * Test checks bad given categoryId
     */
    public function testAdminCategoryActiveIncorrectCategoryId(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category1);
        $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category2, true);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'categoryId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
            'active' => true
        ];

        $this->webClient->request('PATCH', '/api/admin/category/active', server : [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    public function testAdminCategoryActiveEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category1);
        $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category2, true);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
        ];

        $this->webClient->request('PATCH', '/api/admin/category/active', server : [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAdminCategoryActivePermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category1);
        $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category2, true);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'categoryId' => $category1->getId(),
            'active' => true
        ];

        $this->webClient->request('PATCH', '/api/admin/category/active', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAdminCategoryActiveLogOut(): void
    {
        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');

        $content = [
            'categoryId' => $category1->getId(),
            'active' => true
        ];

        $this->webClient->request('PATCH', '/api/admin/category/active', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
