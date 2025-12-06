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

class AdminAudiobookReAddingTest extends AbstractWebTest
{
    private const BASE64_ONE_PART_FILE = __DIR__ . '/onePartFile.txt';
    private const BASE64_RE_ADDING_PART_FILE = __DIR__ . '/reAddingPartFile.txt';
    private const BASE64_FIRST_PART_FILE = __DIR__ . '/firstPartFile.txt';

    /**
     * Test checks a correct readding one part audiobook
     */
    public function testAdminAudiobookReAddingCorrect(): void
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

        $audiobookAfterFirst = $this->databaseMockManager->testFunc_addAudiobookFromFileInOnePart(
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

        $fileBase = fopen(self::BASE64_RE_ADDING_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_RE_ADDING_PART_FILE));

        $content = [
            'audiobookId' => $audiobookAfterFirst->getId(),
            'hashName' => 'c91c03ea6c46a86cbc019be3d71d0a1a',
            'fileName' => 'Base',
            'base64' => $readData,
            'part' => 1,
            'parts' => 1,
            'deleteNotifications' => false,
            'deleteComments' => false,
            'additionalData' => [
                'categories' => [
                    $category2->getId()
                ],
                'title' => 'tytul2',
                'author' => 'author2',
                'age' => 2,
                'year' => '27.11.2022'
            ]
        ];

        $this->webClient->request('PATCH', '/api/admin/audiobook/reAdding', server : [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $audiobookAfter = $audiobookRepository->findOneBy([
            'title' => $content['additionalData']['title']
        ]);

        $this->assertNotNull($audiobookAfter);

        $this->assertSame($audiobookAfter->getTitle(), $content['additionalData']['title']);
        $this->assertSame($audiobookAfter->getAuthor(), $content['additionalData']['author']);
        $this->assertNotSame($audiobookAfterFirst->getParts(), $audiobookAfter->getParts());
        $this->assertNotSame($audiobookAfterFirst->getDescription(), $audiobookAfter->getDescription());
        $this->assertNotSame($audiobookAfterFirst->getSize(), $audiobookAfter->getSize());

        $hasSecondCategory = false;

        foreach ($audiobookAfter->getCategories() as $category) {
            if ($category->getId()->__toString() === $category2->getId()->__toString()) {
                $hasSecondCategory = true;
            }
        }

        $this->assertTrue($hasSecondCategory);

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }

    /**
     * Test checks a correct readdding delete comments and notifications
     */
    public function testAdminAudiobookReAddingDeleteCommentsAndNotificationsCorrect(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);
        $notificationRepository = $this->getService(NotificationRepository::class);
        $audiobookUserCommentRepository = $this->getService(AudiobookUserCommentRepository::class);

        $this->assertInstanceOf(AudiobookUserCommentRepository::class, $audiobookUserCommentRepository);
        $this->assertInstanceOf(NotificationRepository::class, $notificationRepository);
        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t1', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], null, (new DateTime())->modify('- 1 month'));

        $comment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment2', $audiobook1, $user, $comment1);
        $comment2 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment3', $audiobook1, $user);
        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user, $comment2);

        $this->databaseMockManager->testFunc_addNotifications([], NotificationType::NEW_AUDIOBOOK, $audiobook1->getId(), NotificationUserType::SYSTEM);
        $this->databaseMockManager->testFunc_addNotifications([], NotificationType::NEW_AUDIOBOOK, $audiobook1->getId(), NotificationUserType::SYSTEM);
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $fileBase = fopen(self::BASE64_RE_ADDING_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_RE_ADDING_PART_FILE));

        $content = [
            'audiobookId' => $audiobook1->getId(),
            'hashName' => 'c91c03ea6c46a86cbc019be3d71d0a1a',
            'fileName' => 'Base',
            'base64' => $readData,
            'part' => 1,
            'parts' => 1,
            'deleteNotifications' => true,
            'deleteComments' => true,
            'additionalData' => [
                'categories' => [
                    $category2->getId()
                ],
                'title' => 'tytul2',
                'author' => 'author2'
            ]
        ];

        $this->webClient->request('PATCH', '/api/admin/audiobook/reAdding', server : [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $audiobookAfter = $audiobookRepository->findOneBy([
            'id' => $audiobook1->getId()
        ]);
        $comments = $audiobookUserCommentRepository->findBy([
            'audiobook' => $audiobook1->getId(),
        ]);
        $this->assertNotNull($audiobookAfter);

        $this->assertCount(0, $comments);
        $this->assertSame($audiobookAfter->getTitle(), $content['additionalData']['title']);
        $this->assertSame($audiobookAfter->getAuthor(), $content['additionalData']['author']);
        $this->assertNotSame($audiobook1->getParts(), $audiobookAfter->getParts());
        $this->assertNotSame($audiobook1->getDescription(), $audiobookAfter->getDescription());
        $this->assertNotSame($audiobook1->getSize(), $audiobookAfter->getSize());
        $this->assertCount(2, $notificationRepository->findBy([
            'actionId' => $audiobook1->getId(),
            'deleted' => true
        ]));

        $this->assertCount(0, $audiobookAfter->getAudiobookUserComments());

        $hasSecondCategory = false;

        foreach ($audiobookAfter->getCategories() as $category) {
            if ($category->getId()->__toString() === $category2->getId()->__toString()) {
                $hasSecondCategory = true;
            }
        }

        $this->assertTrue($hasSecondCategory);

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }

    /**
     * Test checks a correct readding only one part
     */
    public function testAdminAudiobookReAddingFirstPartCorrect(): void
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

        $audiobookAfterFirst = $this->databaseMockManager->testFunc_addAudiobookFromFileInOnePart(
            hashName: 'c91c03ea6c46a86cbc019be3d71d0a1a',
            fileName: 'Base',
            base64: $readData,
            categories: [
                $category2->getId()->__toString(),
                $category1->getId()->__toString()
            ],
            title: 'tytul2',
            author: 'author'
        );

        $fileBase = fopen(self::BASE64_FIRST_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_FIRST_PART_FILE));

        $content = [
            'audiobookId' => $audiobookAfterFirst->getId(),
            'hashName' => 'c91c03ea6c46a86cbc019be3d71d0a1a',
            'fileName' => 'Base',
            'base64' => $readData,
            'part' => 1,
            'parts' => 2,
            'deleteNotifications' => false,
            'deleteComments' => false,
            'additionalData' => [
                'categories' => [
                    $category2->getId()
                ],
                'title' => 'tytul2',
                'author' => 'author2'
            ]
        ];

        $this->webClient->request('PATCH', '/api/admin/audiobook/reAdding', server : [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $audiobookAfter = $audiobookRepository->findOneBy([
            'title' => 'tytul2'
        ]);
        $this->assertNotNull($audiobookAfter);

        $audiobookService->removeFolder($audiobookAfter->getFileName());

        $audiobookService->removeFolder($_ENV['MAIN_DIR'] . '/' . $content['hashName']);
    }

    /**
     * Test checks a correct readding of a one part audiobook with no additional data
     */
    public function testAdminAudiobookReAddingNoAdditionalDataCorrect(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        $fileBase = fopen(self::BASE64_ONE_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_ONE_PART_FILE));

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $audiobookAfterFirst = $this->databaseMockManager->testFunc_addAudiobookFromFileInOnePart(
            hashName: 'c91c03ea6c46a86cbc019be3d71d0a1a',
            fileName: 'Base',
            base64: $readData,
            categories: [],
            title: 'tytul',
            author: 'author'
        );

        $fileBase = fopen(self::BASE64_RE_ADDING_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_RE_ADDING_PART_FILE));

        $content = [
            'audiobookId' => $audiobookAfterFirst->getId(),
            'hashName' => 'c91c03ea6c46a86cbc019be3d71d0a1a',
            'fileName' => 'Base',
            'base64' => $readData,
            'part' => 1,
            'parts' => 1,
            'deleteNotifications' => false,
            'deleteComments' => false,
            'additionalData' => [
            ]
        ];

        $this->webClient->request('PATCH', '/api/admin/audiobook/reAdding', server : [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $audiobookAfter = $audiobookRepository->findAll()[0];

        $this->assertNotNull($audiobookAfter);

        $this->assertNotSame($audiobookAfterFirst->getDescription(), $audiobookAfter->getDescription());
        $this->assertNotSame($audiobookAfterFirst->getSize(), $audiobookAfter->getSize());

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }

    public function testAdminAudiobookReAddingEmptyRequestData(): void
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

        $this->webClient->request('PATCH', '/api/admin/audiobook/reAdding', server : [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData($this->webClient);

        $audiobookAfter = $audiobookRepository->findAll()[0];

        $this->assertNotNull($audiobookAfter);

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }

    public function testAdminAudiobookReAddingPermission(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);

        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Recruiter'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $audiobookAfterFirst = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd6', [$category2]);

        $fileBase = fopen(self::BASE64_RE_ADDING_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_RE_ADDING_PART_FILE));

        $content = [
            'audiobookId' => $audiobookAfterFirst->getId(),
            'hashName' => 'c91c03ea6c46a86cbc019be3d71d0a1a',
            'fileName' => 'Base',
            'base64' => $readData,
            'part' => 1,
            'parts' => 1,
            'deleteNotifications' => false,
            'deleteComments' => false,
            'additionalData' => [
                'categories' => [
                    $category2->getId()
                ],
                'title' => 'tytul2',
                'author' => 'author2'
            ]
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/admin/audiobook/reAdding', server : [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAdminAudiobookReAddingLogOut(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);

        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);

        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $audiobookAfterFirst = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd6', [$category2]);

        $fileBase = fopen(self::BASE64_RE_ADDING_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_RE_ADDING_PART_FILE));

        $content = [
            'audiobookId' => $audiobookAfterFirst->getId(),
            'hashName' => 'c91c03ea6c46a86cbc019be3d71d0a1a',
            'fileName' => 'Base',
            'base64' => $readData,
            'part' => 1,
            'parts' => 1,
            'deleteNotifications' => false,
            'deleteComments' => false,
            'additionalData' => [
                'categories' => [
                    $category2->getId()
                ],
                'title' => 'tytul2',
                'author' => 'author2'
            ]
        ];

        $this->webClient->request('PATCH', '/api/admin/audiobook/reAdding', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
