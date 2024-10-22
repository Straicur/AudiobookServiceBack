<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminAudiobookCategoryController;

use App\Repository\AudiobookCategoryRepository;
use App\Tests\AbstractWebTest;

/**
 * AdminCategoryActiveTest
 */
class AdminCategoryActiveTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if category is active
     * @return void
     */
    public function test_adminCategoryActiveCorrect(): void
    {
        $audiobookCategoryRepository = $this->getService(AudiobookCategoryRepository::class);

        $this->assertInstanceOf(AudiobookCategoryRepository::class, $audiobookCategoryRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        /// step 2
        $content = [
            'categoryId' => $category1->getId(),
            'active' => true
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('PATCH', '/api/admin/category/active', server : [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $category1After = $audiobookCategoryRepository->findOneBy([
            'id' => $category1->getId()
        ]);

        $this->assertTrue($category1After->getActive());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if category is not active
     * @return void
     */
    public function test_adminCategoryNotActiveCorrect(): void
    {
        $audiobookCategoryRepository = $this->getService(AudiobookCategoryRepository::class);

        $this->assertInstanceOf(AudiobookCategoryRepository::class, $audiobookCategoryRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        /// step 2
        $content = [
            'categoryId' => $category1->getId(),
            'active' => false
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('PATCH', '/api/admin/category/active', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $category1After = $audiobookCategoryRepository->findOneBy([
            'id' => $category1->getId()
        ]);

        $this->assertFalse($category1After->getActive());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad ServiceId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminCategoryActiveIncorrectCategoryId(): void
    {
        /// step 1
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

        /// step 2
        self::$webClient->request('PATCH', '/api/admin/category/active', server : [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 4
        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminCategoryActiveEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category1);
        $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category2, true);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
        ];

        /// step 2
        self::$webClient->request('PATCH', '/api/admin/category/active', server : [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad permission
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminCategoryActivePermission(): void
    {
        /// step 1
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

        /// step 2
        self::$webClient->request('PATCH', '/api/admin/category/active', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without token
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminCategoryActiveLogOut(): void
    {
        /// step 1
        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');

        /// step 2
        $content = [
            'categoryId' => $category1->getId(),
            'active' => true
        ];

        /// step 2
        self::$webClient->request('PATCH', '/api/admin/category/active', content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
