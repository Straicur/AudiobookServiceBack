<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminAudiobookCategoryController;

use App\Tests\AbstractWebTest;

class AdminCategoriesTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response has returned correct data
     * @return void
     */
    public function test_adminCategoriesCorrect(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category1);
        $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category2, true);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('GET', '/api/admin/categories', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('categories', $responseContent);
        $this->assertCount(16, $responseContent['categories']);
    }

    public function test_adminCategoriesPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category1);
        $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category2, true);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('GET', '/api/admin/categories', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_adminCategoriesLogOut(): void
    {
        self::$webClient->request('GET', '/api/admin/categories');

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
