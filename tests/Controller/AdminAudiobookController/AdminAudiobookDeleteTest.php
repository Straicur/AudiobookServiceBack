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

/**
 * AdminAudiobookDeleteTest
 */
class AdminAudiobookDeleteTest extends AbstractWebTest
{
    private const BASE64_ONE_PART_FILE = __DIR__ . '/onePartFile.txt';

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if audiobook is added and categories are correct
     * @return void
     */
    public function test_adminAudiobookDeleteCorrect(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookUserCommentRepository = $this->getService(AudiobookUserCommentRepository::class);

        $this->assertInstanceOf(AudiobookUserCommentRepository::class, $audiobookUserCommentRepository);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $fileBase = fopen(self::BASE64_ONE_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_ONE_PART_FILE,));

        /// step 2
        $content = [
            'hashName' => 'c91c03ea6c46a86cbc019be3d71d0a1a',
            'fileName' => 'Base',
            'base64' => $readData,
            'part' => 1,
            'parts' => 1,
            'additionalData' => [
                'categories' => [
                    $category2->getId(),
                    $category1->getId()
                ],
                'title' => 'tytul',
                'author' => 'author'
            ]
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request('PUT', '/api/admin/audiobook/add', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $audiobookAfter = $audiobookRepository->findOneBy([
            'title' => $content['additionalData']['title']
        ]);

        $this->assertNotNull($audiobookAfter);

        $content = [
            'audiobookId' => $audiobookAfter->getId(),
        ];
        $id = $audiobookAfter->getId();
        $dir = $audiobookAfter->getFileName();

        /// step 3
        $crawler = self::$webClient->request('DELETE', '/api/admin/audiobook/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $audiobooksAfter = $audiobookRepository->findAll();

        $this->assertCount(0, $audiobooksAfter);
        $this->assertCount(0, $audiobookUserCommentRepository->findBy([
            'audiobook' => $id
        ]));

        $this->assertFalse(is_dir($dir));

    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if audiobook notifications are deleted
     * @return void
     */
    public function test_adminAudiobookDeleteAllNotificationsCorrect(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $notificationRepository = $this->getService(NotificationRepository::class);

        $this->assertInstanceOf(NotificationRepository::class, $notificationRepository);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123125', ['Guest', 'User'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123126', ['Guest', 'User'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category2);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category4);
        $category6 = $this->databaseMockManager->testFunc_addAudiobookCategory('6');

        $audiobook = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd', [$category1, $category2]);

        $notification1 = $this->databaseMockManager->testFunc_addNotifications([$user1, $user2], NotificationType::NEW_CATEGORY, $category2->getId(), NotificationUserType::SYSTEM, categoryKey: $category2->getCategoryKey());
        $notification2 = $this->databaseMockManager->testFunc_addNotifications([$user1, $user2], NotificationType::NEW_AUDIOBOOK, $audiobook->getId(), NotificationUserType::SYSTEM);

        $this->databaseMockManager->testFunc_addNotificationCheck($user1, $notification2);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'audiobookId' => $audiobook->getId(),
        ];

        /// step 3
        $crawler = self::$webClient->request('DELETE', '/api/admin/audiobook/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $not1After = $notificationRepository->findOneBy([
            'id' => $notification1->getId()
        ]);
        $this->assertNotNull($not1After);
        $this->assertFalse($not1After->getDeleted());

        $not2After = $notificationRepository->findOneBy([
            'id' => $notification2->getId()
        ]);
        $this->assertNotNull($not2After);
        $this->assertTrue($not2After->getDeleted());
        $this->assertNotNull($not2After->getDateDeleted());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminAudiobookDeleteWrongAudiobookId(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);

        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $fileBase = fopen(self::BASE64_ONE_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_ONE_PART_FILE,));

        /// step 2
        $content = [
            'hashName' => 'c91c03ea6c46a86cbc019be3d71d0a1a',
            'fileName' => 'Base',
            'base64' => $readData,
            'part' => 1,
            'parts' => 1,
            'additionalData' => [
                'categories' => [
                    $category2->getId(),
                    $category1->getId()
                ],
                'title' => 'tytul',
                'author' => 'author'
            ]
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request('PUT', '/api/admin/audiobook/add', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $audiobookAfter = $audiobookRepository->findOneBy([
            'title' => $content['additionalData']['title']
        ]);

        $this->assertNotNull($audiobookAfter);

        $content = [
            'audiobookId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
        ];

        $dir = $audiobookAfter->getFileName();

        /// step 3
        $crawler = self::$webClient->request('DELETE', '/api/admin/audiobook/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(404);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('error', $responseContent);
        $this->assertArrayHasKey('data', $responseContent);

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminAudiobookDeleteEmptyRequestData(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);

        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $fileBase = fopen(self::BASE64_ONE_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_ONE_PART_FILE,));

        /// step 2
        $content = [
            'hashName' => 'c91c03ea6c46a86cbc019be3d71d0a1a',
            'fileName' => 'Base',
            'base64' => $readData,
            'part' => 1,
            'parts' => 1,
            'additionalData' => [
                'categories' => [
                    $category2->getId(),
                    $category1->getId()
                ],
                'title' => 'tytul',
                'author' => 'author'
            ]
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request('PUT', '/api/admin/audiobook/add', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $audiobookAfter = $audiobookRepository->findOneBy([
            'title' => $content['additionalData']['title']
        ]);

        $this->assertNotNull($audiobookAfter);

        $content = [];

        $dir = $audiobookAfter->getFileName();

        /// step 3
        $crawler = self::$webClient->request('DELETE', '/api/admin/audiobook/delete', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(400);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('error', $responseContent);

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }
//    /**
//     * step 1 - Preparing data
//     * step 2 - Sending Request with bad permission
//     * step 3 - Checking response
//     *
//     * @return void
//     */
//    public function test_adminAudiobookDeletePermission(): void
//    {
//        $audiobookRepository = $this->getService(AudiobookRepository::class);
//
//        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
//        /// step 1
//        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
//        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'tes3@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');
//        $user3 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'tesr4@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');
//
//        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
//        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
//
//        $fileBase = fopen(self::BASE64_ONE_PART_FILE, 'r');
//        $readData = fread($fileBase, filesize(self::BASE64_ONE_PART_FILE,));
//
//        /// step 2
//        $content = [
//            'hashName' => 'c91c03ea6c46a86cbc019be3d71d0a1a',
//            'fileName' => 'Base',
//            'base64' => $readData,
//            'part' => 1,
//            'parts' => 1,
//            'additionalData' => [
//                'categories' => [
//                    $category2->getId(),
//                    $category1->getId()
//                ],
//                'title' => 'tytul',
//                'author' => 'author'
//            ]
//        ];
//        $token = $this->databaseMockManager->testFunc_loginUser($user);
//        /// step 3
//        $crawler = self::$webClient->request('PUT', '/api/admin/audiobook/add', server: [
//            'HTTP_authorization' => $token->getToken()
//        ], content: json_encode($content));
//
//        /// step 4
//        self::assertResponseIsSuccessful();
//        self::assertResponseStatusCodeSame(201);
//
//        $audiobookAfter = $audiobookRepository->findOneBy([
//            'title' => $content['additionalData']['title']
//        ]);
//
//        $this->assertNotNull($audiobookAfter);
//
//        $content = [
//            'audiobookId' => $audiobookAfter->getId(),
//        ];
//
//        $dir = $audiobookAfter->getFileName();
//
//        $token = $this->databaseMockManager->testFunc_loginUser($user2);
//
//        /// step 3
//        $crawler = self::$webClient->request('DELETE', '/api/admin/audiobook/delete', server: [
//            'HTTP_authorization' => $token->getToken()
//        ], content: json_encode($content));
//        /// step 3
//        self::assertResponseStatusCodeSame(403);
//
//        $responseContent = self::$webClient->getResponse()->getContent();
//
//        $this->assertNotNull($responseContent);
//        $this->assertNotEmpty($responseContent);
//        $this->assertJson($responseContent);
//
//        $responseContent = json_decode($responseContent, true);
//
//        $this->assertIsArray($responseContent);
//        $this->assertArrayHasKey('error', $responseContent);
//    }
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without token
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminAudiobookDeleteLogOut(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);

        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $fileBase = fopen(self::BASE64_ONE_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_ONE_PART_FILE,));

        /// step 2
        $content = [
            'hashName' => 'c91c03ea6c46a86cbc019be3d71d0a1a',
            'fileName' => 'Base',
            'base64' => $readData,
            'part' => 1,
            'parts' => 1,
            'additionalData' => [
                'categories' => [
                    $category2->getId(),
                    $category1->getId()
                ],
                'title' => 'tytul',
                'author' => 'author'
            ]
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request('PUT', '/api/admin/audiobook/add', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $audiobookAfter = $audiobookRepository->findOneBy([
            'title' => $content['additionalData']['title']
        ]);

        $this->assertNotNull($audiobookAfter);

        $content = [
            'audiobookId' => $audiobookAfter->getId(),
        ];

        $dir = $audiobookAfter->getFileName();

        /// step 3

        $crawler = self::$webClient->request('DELETE', '/api/admin/audiobook/delete', content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(401);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('error', $responseContent);

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }

}