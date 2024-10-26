<?php

declare(strict_types=1);

namespace App\Tests\Controller\AudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Repository\AudiobookRepository;
use App\Service\Admin\Audiobook\AudiobookService;
use App\Tests\AbstractWebTest;
use DateTime;

class AudiobookPartTest extends AbstractWebTest
{
    public function testAudiobookPartCorrect(): void
    {
        $base64OnePartFile = str_replace('AudiobookController', '', __DIR__) . 'AdminAudiobookController/onePartFile.txt';

        $audiobookRepository = $this->getService(AudiobookRepository::class);
        $audiobookService = $this->getService(AudiobookService::class);

        $this->assertInstanceOf(AudiobookService::class, $audiobookService);
        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $fileBase = fopen($base64OnePartFile, 'rb');
        $readData = fread($fileBase, filesize($base64OnePartFile));

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

        self::$webClient->request('PUT', '/api/admin/audiobook/add', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $audiobookAfter = $audiobookRepository->findOneBy([
            'title' => $content['additionalData']['title']
        ]);

        $this->assertNotNull($audiobookAfter);

        $content2 = [
            'audiobookId' => $audiobookAfter->getId(),
            'part' => 0
        ];

        self::$webClient->request('POST', '/api/audiobook/part', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content2));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('url', $responseContent);

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }

    /**
     * Test checks bad given audiobookId
     */
    public function testAudiobookPartWrongAudiobookId(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content2 = [
            'audiobookId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
            'part' => 0
        ];

        self::$webClient->request('POST', '/api/audiobook/part', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content2));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function testAudiobookPartEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content2 = [];

        self::$webClient->request('POST', '/api/audiobook/part', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content2));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function testAudiobookPartPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category2]);

        $content2 = [
            'audiobookId' => $audiobook->getId(),
            'part' => 0
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('POST', '/api/audiobook/part', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content2));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function testAudiobookPartLogOut(): void
    {
        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [], active: true);

        $content2 = [
            'audiobookId' => $audiobook1->getId(),
            'part' => 0
        ];

        self::$webClient->request('POST', '/api/audiobook/part', content: json_encode($content2));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
