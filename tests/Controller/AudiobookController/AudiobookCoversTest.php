<?php

declare(strict_types=1);

namespace App\Tests\Controller\AudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Repository\AudiobookRepository;
use App\Service\Admin\Audiobook\AudiobookService;
use App\Tests\AbstractWebTest;
use DateTime;

/**
 * AudiobookCoversTest
 */
class AudiobookCoversTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if audiobook is added and categories are correct
     * @return void
     */
    public function test_audiobookCoverCorrect(): void
    {
        $base64OnePartFile = str_replace('AudiobookController', '', __DIR__).'AdminAudiobookController/onePartFile.txt';

        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);

        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $fileBase = fopen($base64OnePartFile, 'rb');
        $readData = fread($fileBase, filesize($base64OnePartFile,));

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

        $content2 = [
            'audiobooks' => [$audiobookAfter->getId()],
        ];

        /// step 3
        $crawler = self::$webClient->request('POST', '/api/audiobook/covers', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content2));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5
        $this->assertIsArray($responseContent);
      
        $this->assertArrayHasKey('audiobookCoversModels', $responseContent);

        $this->assertCount(1,$responseContent['audiobookCoversModels']);
        $this->assertArrayHasKey('id', $responseContent['audiobookCoversModels'][0]);
        $this->assertArrayHasKey('url', $responseContent['audiobookCoversModels'][0]);

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad data
     * step 3 - Checking response if length is correct
     *
     * @return void
     */
    public function test_audiobookCoverIncorrectData(): void
    {
        $base64OnePartFile = str_replace('AudiobookController', '', __DIR__).'AdminAudiobookController/onePartFile.txt';

        $audiobookRepository = $this->getService(AudiobookRepository::class);

        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest','User'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'tes3@cos.pl', '+48123123127', ['Guest', 'User'], true, 'zaq12wsx');
        $user3 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'tesr4@cos.pl', '+48123123126', ['Guest', 'User'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $fileBase = fopen($base64OnePartFile, 'rb');
        $readData = fread($fileBase, filesize($base64OnePartFile,));
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category2]);

        $content2 = [
            'audiobook' => ['321',1],
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request('POST', '/api/audiobook/covers', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content2));
        /// step 3
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5
        $this->assertIsArray($responseContent);

        $this->assertCount(0,$responseContent);
    }
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad permission
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_audiobookCoverPermission(): void
    {
        $base64OnePartFile = str_replace('AudiobookController', '', __DIR__).'AdminAudiobookController/onePartFile.txt';

        $audiobookRepository = $this->getService(AudiobookRepository::class);

        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'tes3@cos.pl', '+48123123128', ['Guest', 'User'], true, 'zaq12wsx');
        $user3 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'tesr4@cos.pl', '+48123123127', ['Guest', 'User'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $fileBase = fopen($base64OnePartFile, 'rb');
        $readData = fread($fileBase, filesize($base64OnePartFile,));
        $audiobook = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category2]);

        $content2 = [
            'audiobook' => [$audiobook->getId()],
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request('POST', '/api/audiobook/covers', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content2));
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
    public function test_audiobookCoverLogOut(): void
    {
        $base64OnePartFile = str_replace('AudiobookController', '', __DIR__).'AdminAudiobookController/onePartFile.txt';

        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);

        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $fileBase = fopen($base64OnePartFile, 'rb');
        $readData = fread($fileBase, filesize($base64OnePartFile,));

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


        $content2 = [
            'audiobooks' => [$audiobookAfter->getId()],
        ];

        /// step 3
        $crawler = self::$webClient->request('POST', '/api/audiobook/covers', content: json_encode($content2));
        /// step 3
        self::assertResponseStatusCodeSame(401);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('error', $responseContent);

        $response = self::$webClient->getResponse();

        /// step 5
        $audiobookAfter = $audiobookRepository->findOneBy([
            'title' => $content['additionalData']['title']
        ]);

        $this->assertNotNull($audiobookAfter);

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }
}