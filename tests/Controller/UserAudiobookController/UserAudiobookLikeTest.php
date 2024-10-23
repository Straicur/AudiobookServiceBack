<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserAudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Repository\MyListRepository;
use App\Tests\AbstractWebTest;
use DateTime;

class UserAudiobookLikeTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response has returned correct data
     * @return void
     */
    public function test_userAudiobookLikeCorrect(): void
    {
        $myListRepository = $this->getService(MyListRepository::class);

        $this->assertInstanceOf(MyListRepository::class, $myListRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t1', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t2', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd2', [$category2]);
        $this->databaseMockManager->testFunc_addAudiobook('t3', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category2]);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => $category1->getCategoryKey()
        ];

        self::$webClient->request('PATCH', '/api/user/audiobook/like', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $userMyList = $myListRepository->findOneBy([
            'user' => $user->getId()
        ]);
        $this->assertCount(1, $userMyList->getAudiobooks());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response has returned correct data
     * @return void
     */
    public function test_userAudiobookUnLikeCorrect(): void
    {
        $myListRepository = $this->getService(MyListRepository::class);

        $this->assertInstanceOf(MyListRepository::class, $myListRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd2', [$category2]);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category2]);

        $this->databaseMockManager->testFunc_addMyList($user, $audiobook1);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => $category1->getCategoryKey()
        ];

        self::$webClient->request('PATCH', '/api/user/audiobook/like', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $userMyList = $myListRepository->findOneBy([
            'user' => $user->getId()
        ]);
        $this->assertCount(0, $userMyList->getAudiobooks());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad CategoryKey
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_userAudiobookLikeIncorrectCategoryKey(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b'
        ];

        self::$webClient->request('PATCH', '/api/user/audiobook/like', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad AudiobookId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_userAudiobookLikeIncorrectAudiobookId(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'audiobookId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
            'categoryKey' => $category1->getCategoryKey()
        ];

        self::$webClient->request('PATCH', '/api/user/audiobook/like', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function test_userAudiobookLikeEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [];

        self::$webClient->request('PATCH', '/api/user/audiobook/like', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_userAudiobookLikePermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => $category1->getCategoryKey()
        ];

        self::$webClient->request('PATCH', '/api/user/audiobook/like', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_userAudiobookLikeLogOut(): void
    {
        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);

        $content = [
            'audiobookId' => $audiobook1->getId(),
            'categoryKey' => $category1->getCategoryKey()
        ];

        self::$webClient->request('PATCH', '/api/user/audiobook/like', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
