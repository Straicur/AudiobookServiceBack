<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminAudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Repository\AudiobookRepository;
use App\Repository\AudiobookUserCommentRepository;
use App\Repository\NotificationRepository;
use App\Service\Admin\Audiobook\AudiobookService;
use App\Tests\AbstractWebTest;
use DateTime;

class AdminAudiobookDeleteTest extends AbstractWebTest
{
    private const BASE64_ONE_PART_FILE = __DIR__ . '/onePartFile.txt';

    /**
     * Test checks a correct audiobook delete
     */
    public function testAdminAudiobookDeleteCorrect(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookUserCommentRepository = $this->getService(AudiobookUserCommentRepository::class);

        $this->assertInstanceOf(AudiobookUserCommentRepository::class, $audiobookUserCommentRepository);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $fileBase = fopen(self::BASE64_ONE_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_ONE_PART_FILE));

        $audiobookAfter = $this->databaseMockManager->testFunc_addAudiobookFromFileInOnePart(
            hashName: 'c91c03ea6c46a86cbc019be3d71d0a1a',
            fileName: 'Base',
            base64: $readData,
            categories: [
                $category2->getId()->__toString(),
                $category1->getId()->__toString()
            ],
            title: 'tytul',
            author: 'author'
        );

        $content = [
            'audiobookId' => $audiobookAfter->getId(),
        ];
        $id = $audiobookAfter->getId();
        $dir = $audiobookAfter->getFileName();

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('DELETE', '/api/admin/audiobook/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $audiobooksAfter = $audiobookRepository->findAll();

        $this->assertCount(0, $audiobooksAfter);
        $this->assertCount(0, $audiobookUserCommentRepository->findBy([
            'audiobook' => $id
        ]));

        $this->assertDirectoryDoesNotExist($dir);
    }

    /**
     * Test checks a correct audiobook delete and delete all notifications
     */
    public function testAdminAudiobookDeleteAllNotificationsCorrect(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $notificationRepository = $this->getService(NotificationRepository::class);

        $this->assertInstanceOf(NotificationRepository::class, $notificationRepository);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123125', ['Guest', 'User'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User'], true, 'zaq12wsx');

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

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'audiobookId' => $audiobook->getId(),
        ];

        $this->webClient->request('DELETE', '/api/admin/audiobook/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $this->entityManager->refresh($notification1);
        $this->assertNotNull($notification1);
        $this->assertFalse($notification1->getDeleted());

        $this->entityManager->refresh($notification2);
        $this->assertNotNull($notification2);
        $this->assertTrue($notification2->getDeleted());
        $this->assertNotNull($notification2->getDateDeleted());
    }

    /**
     * Test checks bad given audiobookId
     */
    public function testAdminAudiobookDeleteWrongAudiobookId(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);

        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $fileBase = fopen(self::BASE64_ONE_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_ONE_PART_FILE));

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $audiobookAfter = $this->databaseMockManager->testFunc_addAudiobookFromFileInOnePart(
            hashName: 'c91c03ea6c46a86cbc019be3d71d0a1a',
            fileName: 'Base',
            base64: $readData,
            categories: [
                $category2->getId()->__toString(),
                $category1->getId()->__toString()
            ],
            title: 'tytul',
            author: 'author'
        );

        $content = [
            'audiobookId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
        ];

        $this->webClient->request('DELETE', '/api/admin/audiobook/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }

    public function testAdminAudiobookDeleteEmptyRequestData(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);

        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $fileBase = fopen(self::BASE64_ONE_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_ONE_PART_FILE));

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $audiobookAfter = $this->databaseMockManager->testFunc_addAudiobookFromFileInOnePart(
            hashName: 'c91c03ea6c46a86cbc019be3d71d0a1a',
            fileName: 'Base',
            base64: $readData,
            categories: [
                $category2->getId()->__toString(),
                $category1->getId()->__toString()
            ],
            title: 'tytul',
            author: 'author'
        );

        $content = [];

        $this->webClient->request('DELETE', '/api/admin/audiobook/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData($this->webClient);

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }

    public function testAdminAudiobookDeleteLogOut(): void
    {
        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [], active: true);

        $content = [
            'audiobookId' => $audiobook1->getId(),
        ];

        $this->webClient->request('DELETE', '/api/admin/audiobook/delete', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
