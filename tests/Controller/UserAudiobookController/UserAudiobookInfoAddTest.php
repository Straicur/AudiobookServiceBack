<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserAudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Repository\AudiobookInfoRepository;
use App\Tests\AbstractWebTest;
use DateTime;

class UserAudiobookInfoAddTest extends AbstractWebTest
{
    public function testUserAudiobookInfoAddCorrect(): void
    {
        $audiobookInfoRepository = $this->getService(AudiobookInfoRepository::class);

        $this->assertInstanceOf(AudiobookInfoRepository::class, $audiobookInfoRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd2', [$category2]);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category2]);

        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => $category1->getCategoryKey(),
            'part' => 1,
            'endedTime' => 21,
            'watched' => true
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PUT', '/api/user/audiobook/info/add', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $this->assertCount(1, $audiobookInfoRepository->findAll());
    }

    public function testUserAudiobookInfoAddCorrectDeactivation(): void
    {
        $audiobookInfoRepository = $this->getService(AudiobookInfoRepository::class);

        $this->assertInstanceOf(AudiobookInfoRepository::class, $audiobookInfoRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd2', [$category2]);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category2]);

        $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook1, 1, 21);
        $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook1, 2, 21, true);

        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => $category1->getCategoryKey(),
            'part' => 1,
            'endedTime' => 21,
            'watched' => true
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PUT', '/api/user/audiobook/info/add', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        $this->assertCount(2, $audiobookInfoRepository->findAll());

        $this->assertCount(1, $audiobookInfoRepository->findBy([
            'active' => true
        ]));
        $this->assertCount(1, $audiobookInfoRepository->findBy([
            'active' => false
        ]));
    }

    /**
     * Test checks bad given audiobookId
     */
    public function testUserAudiobookInfoAddIncorrectAudiobookId(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');

        $content = [
            'audiobookId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
            'categoryKey' => $category1->getCategoryKey(),
            'part' => 1,
            'endedTime' => 21,
            'watched' => true
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PUT', '/api/user/audiobook/info/add', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    /**
     * Test checks bad given categoryKey
     */
    public function testUserAudiobookInfoAddIncorrectCategoryKey(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
            'part' => 1,
            'endedTime' => 21,
            'watched' => true
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PUT', '/api/user/audiobook/info/add', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    public function testUserAudiobookInfoAddEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PUT', '/api/user/audiobook/info/add', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testUserAudiobookInfoAddPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => $category1->getCategoryKey(),
            'part' => 1,
            'endedTime' => 21,
            'watched' => true
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PUT', '/api/user/audiobook/info/add', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testUserAudiobookInfoAddLogOut(): void
    {
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => $category1->getCategoryKey(),
            'part' => 1,
            'endedTime' => 21,
            'watched' => true
        ];

        $this->webClient->request('PUT', '/api/user/audiobook/info/add', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
