<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminTechnicalController;

use App\Tests\AbstractWebTest;

/**
 * AdminTechnicalCacheClearTest
 */
class AdminTechnicalCacheClearTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if category is active
     * @return void
     */
    public function test_adminTechnicalCacheClearCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        /// step 2
        $content = [
            'cacheData' => [
                'pools' => ['AdminCategory', 'AdminAudiobookComments', 'AudiobookComments', 'UserAudiobooks', 'UserAudiobookRating'],
                'admin' => false,
                'user' => false,
                'all' => false,
            ]
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('PATCH', '/api/admin/technical/cache/clear', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if category is active
     * @return void
     */
    public function test_adminTechnicalCacheClearAdminCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        /// step 2
        $content = [
            'cacheData' => [
                'pools' => [],
                'admin' => true,
                'user' => false,
                'all' => false,
            ]
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('PATCH', '/api/admin/technical/cache/clear', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if category is active
     * @return void
     */
    public function test_adminTechnicalCacheClearUserCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        /// step 2
        $content = [
            'cacheData' => [
                'pools' => [],
                'admin' => false,
                'user' => true,
                'all' => false,
            ]
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('PATCH', '/api/admin/technical/cache/clear', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if category is active
     * @return void
     */
    public function test_adminTechnicalCacheClearAllCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        /// step 2
        $content = [
            'cacheData' => [
                'pools' => [],
                'admin' => false,
                'user' => false,
                'all' => true,
            ]
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('PATCH', '/api/admin/technical/cache/clear', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad permission
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_adminTechnicalCacheClearPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $content = [
            'cacheData' => [
                'pools' => [],
                'admin' => false,
                'user' => false,
                'all' => false,
            ]
        ];

        /// step 2
        self::$webClient->request('PATCH', '/api/admin/technical/cache/clear', server: [
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
    public function test_adminTechnicalCacheClearLogOut(): void
    {
        /// step 2
        $content = [
            'cacheData' => [
                'pools' => [],
                'admin' => false,
                'user' => false,
                'all' => false,
            ]
        ];

        /// step 2
        self::$webClient->request('PATCH', '/api/admin/technical/cache/clear', content: json_encode($content));

        /// step 3
        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
