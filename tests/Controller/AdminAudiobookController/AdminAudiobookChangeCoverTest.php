<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminAudiobookController;

use App\Repository\AudiobookRepository;
use App\Service\Admin\Audiobook\AudiobookService;
use App\Tests\AbstractWebTest;

class AdminAudiobookChangeCoverTest extends AbstractWebTest
{
    private const BASE64_ONE_PART_FILE = __DIR__ . '/onePartFile.txt';
    private const base64ImgFile = __DIR__ . '/imgFile.txt';

    /**
     * Test checks a correct add of a one part audiobook and then changing a cover
     */
    public function testAdminAudiobookChangeCoverCorrect(): void
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

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $fileBase2 = fopen(self::base64ImgFile, 'rb');
        $readData2 = fread($fileBase2, filesize(self::base64ImgFile));

        $content2 = [
            'type' => 'jpeg',
            'base64' => $readData2,
            'audiobookId' => $audiobookAfter->getId(),
        ];

        $this->webClient->request('PATCH', '/api/admin/audiobook/change/cover', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content2));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $audiobookService->removeFolder($audiobookAfter->getFileName());
    }

    /**
     * Test checks bad given audiobookId
     */
    public function testAdminAudiobookChangeCoverWrongAudiobookId(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $fileBase2 = fopen(self::base64ImgFile, 'rb');
        $readData2 = fread($fileBase2, filesize(self::base64ImgFile));

        $content2 = [
            'type' => 'jpeg',
            'base64' => $readData2,
            'audiobookId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/admin/audiobook/change/cover', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content2));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    public function testAdminAudiobookChangeCoverEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1', null, true);
        $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content2 = [];

        $this->webClient->request('PATCH', '/api/admin/audiobook/change/cover', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content2));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAdminAudiobookChangeCoverPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'Recruiter'], true, 'zaq12wsx');
        $fileBase2 = fopen(self::base64ImgFile, 'rb');
        $readData2 = fread($fileBase2, filesize(self::base64ImgFile));

        $content2 = [
            'type' => 'jpeg',
            'base64' => $readData2,
            'audiobookId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/admin/audiobook/change/cover', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content2));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAdminAudiobookChangeCoverLogOut(): void
    {
        $content2 = [
            'audiobookId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
        ];

        $this->webClient->request('PATCH', '/api/admin/audiobook/change/cover', content: json_encode($content2));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
