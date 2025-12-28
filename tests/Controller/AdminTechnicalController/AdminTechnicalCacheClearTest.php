<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminTechnicalController;

use App\Tests\AbstractWebTest;

class AdminTechnicalCacheClearTest extends AbstractWebTest
{
    /**
     * Test checks a correct technical cache clear for selected redis keys
     */
    public function testAdminTechnicalCacheClearCorrect(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $content = [
            'cacheData' => [
                'pools' => ['AdminCategory', 'AdminAudiobookComments', 'AudiobookComments', 'UserAudiobooks', 'UserAudiobookRating'],
                'admin' => false,
                'user' => false,
                'all' => false,
            ]
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/admin/technical/cache/clear', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Test checks a correct technical cache clear for admin redis keys
     */
    public function testAdminTechnicalCacheClearAdminCorrect(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $content = [
            'cacheData' => [
                'pools' => [],
                'admin' => true,
                'user' => false,
                'all' => false,
            ]
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/admin/technical/cache/clear', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Test checks a correct technical cache clear for user redis keys
     */
    public function testAdminTechnicalCacheClearUserCorrect(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $content = [
            'cacheData' => [
                'pools' => [],
                'admin' => false,
                'user' => true,
                'all' => false,
            ]
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/admin/technical/cache/clear', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Test checks a correct technical cache clear for all redis keys
     */
    public function testAdminTechnicalCacheClearAllCorrect(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $content = [
            'cacheData' => [
                'pools' => [],
                'admin' => false,
                'user' => false,
                'all' => true,
            ]
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/admin/technical/cache/clear', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
    }

    public function testAdminTechnicalCacheClearPermission(): void
    {
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

        $this->webClient->request('PATCH', '/api/admin/technical/cache/clear', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAdminTechnicalCacheClearLogOut(): void
    {
        $content = [
            'cacheData' => [
                'pools' => [],
                'admin' => false,
                'user' => false,
                'all' => false,
            ]
        ];

        $this->webClient->request('PATCH', '/api/admin/technical/cache/clear', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
