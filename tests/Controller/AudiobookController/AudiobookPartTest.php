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

        $content2 = [
            'audiobookId' => $audiobookAfter->getId(),
            'part' => 0
        ];

        $this->webClient->request('POST', '/api/audiobook/part', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content2));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
        $response = $this->webClient->getResponse();

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

        $this->webClient->request('POST', '/api/audiobook/part', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content2));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    public function testAudiobookPartEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content2 = [];

        $this->webClient->request('POST', '/api/audiobook/part', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content2));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData($this->webClient);
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

        $this->webClient->request('POST', '/api/audiobook/part', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content2));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAudiobookPartLogOut(): void
    {
        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [], active: true);

        $content2 = [
            'audiobookId' => $audiobook1->getId(),
            'part' => 0
        ];

        $this->webClient->request('POST', '/api/audiobook/part', content: json_encode($content2));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
