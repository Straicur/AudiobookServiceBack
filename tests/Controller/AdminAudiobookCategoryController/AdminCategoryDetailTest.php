<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminAudiobookCategoryController;

use App\Enums\AudiobookAgeRange;
use App\Repository\AudiobookCategoryRepository;
use App\Repository\AudiobookRepository;
use App\Tests\AbstractWebTest;
use DateTime;

class AdminCategoryDetailTest extends AbstractWebTest
{
    public function testAdminCategoryDetailCorrect(): void
    {
        $audiobookCategoryRepository = $this->getService(AudiobookCategoryRepository::class);
        $audiobookRepository = $this->getService(AudiobookRepository::class);

        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        $this->assertInstanceOf(AudiobookCategoryRepository::class, $audiobookCategoryRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category2);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category4);
        $this->databaseMockManager->testFunc_addAudiobookCategory('6');

        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd', [$category1,
            $category2]);

        $content = [
            'categoryKey' => $category2->getCategoryKey(),
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('POST', '/api/admin/category/detail', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('name', $responseContent);
        $this->assertArrayHasKey('active', $responseContent);
        $this->assertArrayHasKey('parentCategoryName', $responseContent);

        $this->assertSame($responseContent['name'], $category2->getName());
        $this->assertSame($responseContent['active'], $category2->getActive());
        $this->assertSame($responseContent['name'], $category2->getName());
    }

    /**
     * Test checks bad given categoryKey
     */
    public function testAdminCategoryDetailIncorrectCategoryKey(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $content = [
            'categoryKey' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('POST', '/api/admin/category/detail', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function testAdminCategoryDetailEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('POST', '/api/admin/category/detail', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function testAdminCategoryDetailPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $content = [
            'categoryKey' => $category2->getCategoryKey(),
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('POST', '/api/admin/category/detail', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function testAdminCategoryDetailLogOut(): void
    {
        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $content = [
            'categoryKey' => $category2->getCategoryKey(),
        ];

        self::$webClient->request('POST', '/api/admin/category/detail', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
