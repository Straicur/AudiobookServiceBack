<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminAudiobookCategoryController;

use App\Enums\AudiobookAgeRange;
use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Repository\AudiobookCategoryRepository;
use App\Repository\AudiobookRepository;
use App\Repository\NotificationRepository;
use App\Tests\AbstractWebTest;
use DateTime;

/**
 * AdminCategoryRemoveTest
 */
class AdminCategoryRemoveTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad CategoryId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminCategoryRemoveIncorrectCategoryId(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        /// step 2
        $content = [
            'categoryId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('DELETE', '/api/admin/category/remove', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if category is deleted from system and audiobooks
     * @return void
     */
    public function test_adminCategoryRemoveCorrect(): void
    {
        $audiobookCategoryRepository = $this->getService(AudiobookCategoryRepository::class);
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $notificationRepository = $this->getService(NotificationRepository::class);

        $this->assertInstanceOf(NotificationRepository::class, $notificationRepository);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        $this->assertInstanceOf(AudiobookCategoryRepository::class, $audiobookCategoryRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123126', ['Guest', 'User'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123128', ['Guest', 'User'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category2);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category4);
        $this->databaseMockManager->testFunc_addAudiobookCategory('6');

        $audiobook = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd', [$category1, $category2]);

        $notification1 = $this->databaseMockManager->testFunc_addNotifications([$user1, $user2], NotificationType::NEW_CATEGORY, $category2->getId(), NotificationUserType::SYSTEM, categoryKey: $category2->getCategoryKey());
        $notification2 = $this->databaseMockManager->testFunc_addNotifications([$user1, $user2], NotificationType::NEW_AUDIOBOOK, $audiobook->getId(), NotificationUserType::SYSTEM);

        $this->databaseMockManager->testFunc_addNotificationCheck($user1, $notification2);

        /// step 2
        $content = [
            'categoryId' => $category2->getId(),
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('DELETE', '/api/admin/category/remove', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $not1After = $notificationRepository->findOneBy([
            'id' => $notification1->getId()
        ]);
        $this->assertNotNull($not1After);
        $this->assertTrue($not1After->getDeleted());
        $this->assertNotNull($not1After->getDateDeleted());

        $not2After = $notificationRepository->findOneBy([
            'id' => $notification2->getId()
        ]);
        $this->assertNotNull($not2After);
        $this->assertFalse($not2After->getDeleted());


        $this->assertCount(13, $audiobookCategoryRepository->findAll());

        $audiobookAfter = $audiobookRepository->findOneBy([
            'id' => $audiobook->getId()
        ]);

        $categories = $audiobookAfter->getCategories();

        $hasCategory = false;

        foreach ($categories as $category) {
            if ($category === $category2) {
                $hasCategory = true;
            }
        }

        $this->assertFalse($hasCategory);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminCategoryRemoveEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        /// step 2
        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('DELETE', '/api/admin/category/remove', server: [
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
    public function test_adminCategoryRemovePermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        /// step 2
        $content = [
            'categoryId' => $category2->getId(),
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('DELETE', '/api/admin/category/remove', server: [
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
    public function test_adminCategoryRemoveLogOut(): void
    {
        /// step 1
        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        /// step 2
        $content = [
            'categoryId' => $category2->getId(),
        ];
        /// step 3
        self::$webClient->request('DELETE', '/api/admin/category/remove', content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
