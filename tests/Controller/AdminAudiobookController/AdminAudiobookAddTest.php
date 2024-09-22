<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminAudiobookController;

use App\Repository\AudiobookRepository;
use App\Service\Admin\Audiobook\AudiobookService;
use App\Tests\AbstractWebTest;

/**
 * AdminAudiobookAddTest
 */
class AdminAudiobookAddTest extends AbstractWebTest
{
    private const BASE64_ONE_PART_FILE = __DIR__ . '/onePartFile.txt';
    private const BASE64_FIRST_PART_FILE = __DIR__ . '/firstPartFile.txt';
    private const base64SecondPartFile = __DIR__ . '/secondPartFile.txt';

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

        $audiobookAfter = $audiobookRepository->findOneBy([
            'title' => $content['additionalData']['title']
        ]);

        $this->assertNotNull($audiobookAfter);

        $hasFirstCategory = false;
        $hasSecondCategory = false;

        foreach ($audiobookAfter->getCategories() as $category) {
            if ($category->getId()->__toString() == $category1->getId()->__toString()) {
                $hasFirstCategory = true;
            }
            if ($category->getId()->__toString() == $category2->getId()->__toString()) {
                $hasSecondCategory = true;
            }
        }

        $this->assertTrue($hasFirstCategory);
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

        $fileFirstBase = fopen(self::BASE64_ONE_PART_FILE, 'rb');
        $readFirstData = fread($fileFirstBase, filesize(self::BASE64_FIRST_PART_FILE,));

        $content = [
            'hashName' => 'c91c03ea6c46a86cbc019be3d71d0a1a',
            'fileName' => 'Base',
            'base64' => $readFirstData,
            'part' => 1,
            'parts' => 2,
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

        $audiobooksAfter = $audiobookRepository->findAll();

        $this->assertNotNull($audiobooksAfter);

        $audiobookService->removeFolder($audiobooksAfter[0]->getFileName());
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
//        $fileFirstBase = fopen(self::BASE64_ONE_PART_FILE, 'r');
//        $readFirstData = fread($fileFirstBase, filesize(self::BASE64_FIRST_PART_FILE,));
//
//        $content = [
//            'hashName' => 'c91c03ea6c46a86cbc019be3d71d0a1b',
//            'fileName' => 'Base',
//            'base64' => $readFirstData,
//            'part' => 1,
//            'parts' => 2,
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
//        self::assertResponseStatusCodeSame(200);
//
//        $fileSecondBase = fopen(self::BASE64_ONE_PART_FILE, 'r');
//        $readSecondData = fread($fileSecondBase, filesize(self::base64SecondPartFile,));
//
//        /// step 2
//        $content = [
//            'hashName' => 'c91c03ea6c46a86cbc019be3d71d0a1b',
//            'fileName' => 'Base',
//            'base64' => $readSecondData,
//            'part' => 2,
//            'parts' => 2,
//            'additionalData' => [
//                'categories' => [
//                    $category2->getId(),
//                    $category1->getId()
//                ],
//                'title' => 'tytul',
//                'author' => 'author'
//            ]
//        ];
//
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
//        $hasFirstCategory = false;
//        $hasSecondCategory = false;
//
//        foreach ($audiobookAfter->getCategories() as $category) {
//            if ($category->getId()->__toString() == $category->getId()->__toString()) {
//                $hasFirstCategory = true;
//            }
//            if ($category->getId()->__toString() == $category2->getId()->__toString()) {
//                $hasSecondCategory = true;
//            }
//        }
//
//        $this->assertTrue($hasFirstCategory);
//        $this->assertTrue($hasSecondCategory);
//
//        $audiobookService->removeFolder($audiobookAfter->getFileName());
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
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $fileBase = fopen(self::BASE64_ONE_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_ONE_PART_FILE,));

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $crawler = self::$webClient->request('PUT', '/api/admin/audiobook/add', server: [
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
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Recruiter'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $fileBase = fopen(self::BASE64_ONE_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_ONE_PART_FILE,));

        $content = [
            'hashName' => 'c91c03ea6c46a86cbc019be3d71d0a1a',
            'fileName' => 'Base',
            'base64' => $readData,
            'part' => 1,
            'parts' => 1,
            'additionalData' => [
                'categories' => [
                    $category2->getId()
                ],
                'title' => 'tytul',
                'author' => 'author'
            ]
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $crawler = self::$webClient->request('PUT', '/api/admin/audiobook/add', server: [
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
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $fileBase = fopen(self::BASE64_ONE_PART_FILE, 'rb');
        $readData = fread($fileBase, filesize(self::BASE64_ONE_PART_FILE,));

        $content = [
            'hashName' => 'c91c03ea6c46a86cbc019be3d71d0a1a',
            'fileName' => 'Base',
            'base64' => $readData,
            'part' => 1,
            'parts' => 1,
            'additionalData' => [
                'categories' => [
                    $category2->getId()
                ],
                'title' => 'tytul',
                'author' => 'author'
            ]
        ];

        /// step 2
        $crawler = self::$webClient->request('PUT', '/api/admin/audiobook/add', content: json_encode($content));
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