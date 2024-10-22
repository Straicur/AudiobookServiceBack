<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserAudiobookController;

use App\Enums\AudiobookAgeRange;
use App\Tests\AbstractWebTest;
use DateTime;

/**
 * UserAudiobooksSearchTest
 */
class UserAudiobooksSearchTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response has returned correct data
     * @return void
     */
    public function test_userAudiobooksSearchTitleCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category2, true);

        $this->databaseMockManager->testFunc_addAudiobook('tt1', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1,
            $category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('a2', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd2', [$category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('dt3', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('b4', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd4', [$category1,
            $category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t5', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd5', [$category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('n6', 'trhrthr', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd6', [$category5]);
        $this->databaseMockManager->testFunc_addAudiobook('m7', 'trtr', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd7', [$category1,
            $category4], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('j8', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd8', [$category4]);
        $this->databaseMockManager->testFunc_addAudiobook('aaat9', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd9', [$category4], active: true);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'title'       => 't',
            'categoryKey' => '',
        ];

        /// step 2
        self::$webClient->request('POST', '/api/user/audiobooks/search', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5
        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('audiobooks', $responseContent);
        $this->assertCount(5, $responseContent['audiobooks']);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response has returned correct data
     * @return void
     */
    public function test_userAudiobooksSearchCategoryKeyCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category2, true);

        $this->databaseMockManager->testFunc_addAudiobook('tt1', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1,
            $category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('a2', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd2', [$category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('dt3', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('b4', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd4', [$category1,
            $category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t5', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd5', [$category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('n6', 'trhrthr', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd6', [$category5]);
        $this->databaseMockManager->testFunc_addAudiobook('m7', 'trtr', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd7', [$category1,
            $category4], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('j8', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd8', [$category4]);
        $this->databaseMockManager->testFunc_addAudiobook('aaat9', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd9', [$category4], active: true);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'title'       => '',
            'categoryKey' => $category2->getCategoryKey(),
        ];

        /// step 2
        self::$webClient->request('POST', '/api/user/audiobooks/search', server : [
            'HTTP_authorization' => $token->getToken(),
        ], content: json_encode($content));

        /// step 3
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5
        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('audiobooks', $responseContent);

        $this->assertCount(5, $responseContent['audiobooks']);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request
     * step 3 - Checking response
     * step 4 - Checking response has returned correct data
     * @return void
     */
    public function test_userAudiobooksSearchParentControlCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', birthday: (new DateTime())->modify('-14 year'));

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category2, true);

        $this->databaseMockManager->testFunc_addAudiobook('tt1', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::FROM3TO7, 'd1', [$category1,
            $category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('a2', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd2', [$category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('dt3', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::FROM7TO12, 'd3', [$category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('b4', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd4', [$category1,
            $category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t5', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::FROM12TO16, 'd5', [$category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('n6', 'trhrthr', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::FROM12TO16, 'd6', [$category5]);
        $this->databaseMockManager->testFunc_addAudiobook('m7', 'trtr', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::FROM16TO18, 'd7', [$category1,
            $category4], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('j8', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::FROM16TO18, 'd8', [$category4]);
        $this->databaseMockManager->testFunc_addAudiobook('aaat9', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd9', [$category4], active: true);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'title'       => 't',
            'categoryKey' => '',
        ];

        /// step 2
        self::$webClient->request('POST', '/api/user/audiobooks/search', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 3
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5
        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('audiobooks', $responseContent);
        $this->assertCount(3, $responseContent['audiobooks']);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userAudiobooksSearchEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [];

        /// step 2
        self::$webClient->request('POST', '/api/user/audiobooks/search', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));


        /// step 3
        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad permission
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userAudiobooksSearchPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        $content = [
            'title'       => 't',
            'categoryKey' => '',
        ];

        /// step 2
        self::$webClient->request('POST', '/api/user/audiobooks/search', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without token
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userAudiobooksSearchLogOut(): void
    {
        /// step 1
        $content = [
            'title'       => 't',
            'categoryKey' => '',
        ];

        /// step 2
        self::$webClient->request('POST', '/api/user/audiobooks/search', content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
