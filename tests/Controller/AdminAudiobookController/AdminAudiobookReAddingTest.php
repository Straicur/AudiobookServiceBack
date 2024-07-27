<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminAudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Repository\AudiobookRepository;
use App\Repository\AudiobookUserCommentRepository;
use App\Repository\NotificationRepository;
use App\Service\AudiobookService;
use App\Tests\AbstractWebTest;
use DateTime;

/**
 * AdminAudiobookReAddingTest
 */
class AdminAudiobookReAddingTest extends AbstractWebTest
{
    private const BASE64_ONE_PART_FILE = __DIR__ . '/onePartFile.txt';
    private const BASE64_RE_ADDING_PART_FILE = __DIR__ . '/reAddingPartFile.txt';
    private const BASE64_FIRST_PART_FILE = __DIR__ . '/firstPartFile.txt';

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if audiobook is added and categories are correct
     * @return void
     */
    public function test_adminAudiobookAddCorrect(): void
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
                'author' => 'author',
                'age' => 2,
                'year' => '27.11.2022'
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

        $audiobookAfterFirst = $audiobookRepository->findOneBy([
            'title' => $content['additionalData']['title']
        ]);

        $this->assertNotNull($audiobookAfterFirst);

        $fileBase = fopen(self::BASE64_RE_ADDING_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_RE_ADDING_PART_FILE,));

        /// step 2
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
        /// step 3
        $crawler = self::$webClient->request('PATCH', '/api/admin/audiobook/reAdding', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
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
            if ($category->getId()->__toString() == $category2->getId()->__toString()) {
                $hasSecondCategory = true;
            }
        }

        $this->assertTrue($hasSecondCategory);

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if audiobook is added and categories are correct
     * @return void
     */
    public function test_adminAudiobookAddDeleteCommentsAndNotificationsCorrect(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);
        $notificationRepository = $this->getService(NotificationRepository::class);
        $audiobookUserCommentRepository = $this->getService(AudiobookUserCommentRepository::class);

        $this->assertInstanceOf(AudiobookUserCommentRepository::class, $audiobookUserCommentRepository);
        $this->assertInstanceOf(NotificationRepository::class, $notificationRepository);
        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t1', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], null, (new DateTime())->modify('- 1 month'));

        $coment1 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user);
        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment2', $audiobook1, $user, $coment1);
        $coment2 = $this->databaseMockManager->testFunc_addAudiobookUserComment('comment3', $audiobook1, $user);
        $this->databaseMockManager->testFunc_addAudiobookUserComment('comment1', $audiobook1, $user, $coment2);

        $this->databaseMockManager->testFunc_addNotifications([], NotificationType::NEW_AUDIOBOOK, $audiobook1->getId(), NotificationUserType::SYSTEM);
        $this->databaseMockManager->testFunc_addNotifications([], NotificationType::NEW_AUDIOBOOK, $audiobook1->getId(), NotificationUserType::SYSTEM);
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $fileBase = fopen(self::BASE64_RE_ADDING_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_RE_ADDING_PART_FILE));

        /// step 2
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
        /// step 3
        $crawler = self::$webClient->request('PATCH', '/api/admin/audiobook/reAdding', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
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
            if ($category->getId()->__toString() == $category2->getId()->__toString()) {
                $hasSecondCategory = true;
            }
        }

        $this->assertTrue($hasSecondCategory);

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if audiobook is added and categories are correct
     * @return void
     */
    public function test_adminAudiobookAddFirstPartCorrect(): void
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
        $content1 = [
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
        ], content: json_encode($content1));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $audiobookAfterFirst = $audiobookRepository->findOneBy([
            'title' => $content1['additionalData']['title']
        ]);

        $this->assertNotNull($audiobookAfterFirst);

        $fileBase = fopen(self::BASE64_FIRST_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_FIRST_PART_FILE,));

        /// step 2
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
        /// step 3
        $crawler = self::$webClient->request('PATCH', '/api/admin/audiobook/reAdding', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $audiobookAfter = $audiobookRepository->findOneBy([
            'title' => $content1['additionalData']['title']
        ]);
        $this->assertNotNull($audiobookAfter);

        $audiobookService->removeFolder($audiobookAfter->getFileName());

        $audiobookService->removeFolder($_ENV['MAIN_DIR'] . '/' . $content['hashName']);

    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if audiobook is added without additional data
     * @return void
     */
    public function test_adminAudiobookAddNoAdditionalDataCorrect(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        $fileBase = fopen(self::BASE64_ONE_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_ONE_PART_FILE,));

        /// step 2
        $content = [
            'hashName' => 'c91c03ea6c46a86cbc019be3d71d0a1a',
            'fileName' => 'Base',
            'base64' => $readData,
            'part' => 1,
            'parts' => 1,
            'additionalData' => []
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request('PUT', '/api/admin/audiobook/add', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $audiobookAfterFirst = $audiobookRepository->findAll()[0];

        $this->assertNotNull($audiobookAfterFirst);

        $fileBase = fopen(self::BASE64_RE_ADDING_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_RE_ADDING_PART_FILE,));

        /// step 2
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
        /// step 3
        $crawler = self::$webClient->request('PATCH', '/api/admin/audiobook/reAdding', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $audiobookAfter = $audiobookRepository->findAll()[0];

        $this->assertNotNull($audiobookAfter);

        $this->assertNotSame($audiobookAfterFirst->getParts(), $audiobookAfter->getParts());
        $this->assertNotSame($audiobookAfterFirst->getDescription(), $audiobookAfter->getDescription());
        $this->assertNotSame($audiobookAfterFirst->getSize(), $audiobookAfter->getSize());

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }
//    /**
//     * step 1 - Preparing data
//     * step 2 - Preparing JsonBodyContent
//     * step 3 - Sending Request
//     * step 4 - Checking response
//     * step 5 - Checking response if audiobook is added and categories are correct
//     * @return void
//     */
//    public function test_adminAudiobookAddPartsCorrect(): void
//    {
//        $audiobookRepository = $this->getService(AudiobookRepository::class);
//        $audiobookService = $this->getService(AudiobookService::class);
//
//        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
//        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
//        /// step 1
//        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
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
//        $audiobookAfterFirst = $audiobookRepository->findOneBy([
//            'title' => $content['additionalData']['title']
//        ]);
//
//        $this->assertNotNull($audiobookAfterFirst);
//
//        $fileBase = fopen(self::BASE64_FIRST_PART_FILE, 'r');
//        $readData = fread($fileBase, filesize(self::BASE64_FIRST_PART_FILE,));
//
//        /// step 2
//        $content = [
//            'audiobookId'=>$audiobookAfterFirst->getId(),
//            'hashName' => 'c91c03ea6c46a86cbc019be3d71d0a1a',
//            'fileName' => 'Base',
//            'base64' => $readData,
//            'part' => 1,
//            'parts' => 1,
//            'additionalData' => [
//                'categories' => [
//                    $category2->getId()
//                ],
//                'title' => 'tytul2',
//                'author' => 'author2'
//            ]
//        ];
//        /// step 3
//        $crawler = self::$webClient->request('PATCH', '/api/admin/audiobook/reAdding', server: [
//            'HTTP_authorization' => $token->getToken()
//        ], content: json_encode($content));
//
//        /// step 4
//        self::assertResponseIsSuccessful();
//        self::assertResponseStatusCodeSame(200);
//    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminAudiobookAddEmptyRequestData(): void
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

        $audiobookAfterFirst = $audiobookRepository->findOneBy([
            'title' => $content['additionalData']['title']
        ]);

        $this->assertNotNull($audiobookAfterFirst);

        $fileBase = fopen(self::BASE64_RE_ADDING_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_RE_ADDING_PART_FILE,));

        /// step 2
        $content = [];
        /// step 3
        $crawler = self::$webClient->request('PATCH', '/api/admin/audiobook/reAdding', server: [
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

        $audiobookAfter = $audiobookRepository->findAll()[0];

        $this->assertNotNull($audiobookAfter);

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad permission
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminAudiobookAddPermission(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);

        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Recruiter'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $audiobookAfterFirst = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd6', [$category2]);

        $fileBase = fopen(self::BASE64_RE_ADDING_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_RE_ADDING_PART_FILE,));

        /// step 2
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
        /// step 3
        $crawler = self::$webClient->request('PATCH', '/api/admin/audiobook/reAdding', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(403);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('error', $responseContent);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without token
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminAudiobookAddLogOut(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);

        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $audiobookAfterFirst = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd6', [$category2]);

        $fileBase = fopen(self::BASE64_ONE_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_ONE_PART_FILE,));

        $fileBase = fopen(self::BASE64_RE_ADDING_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_RE_ADDING_PART_FILE,));

        /// step 2
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
        /// step 3
        $crawler = self::$webClient->request('PATCH', '/api/admin/audiobook/reAdding', content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(401);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('error', $responseContent);
    }
}