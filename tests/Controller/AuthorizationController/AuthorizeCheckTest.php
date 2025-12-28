<?php

declare(strict_types=1);

namespace App\Tests\Controller\AuthorizationController;

use App\Tests\AbstractWebTest;
use DateTime;

class AuthorizeCheckTest extends AbstractWebTest
{
    public function testAuthorizeCheckCorrect(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@audiobookback.icu', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [
            'email' => 'test@audiobookback.icu',
            'password' => 'zaq12wsx'
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('POST', '/api/authorize/check', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
    }

    public function testAuthorizeCheckTokenExpired(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@audiobookback.icu', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [
            'email' => 'test@audiobookback.icu',
            'password' => 'zaq12wsx'
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user, (new DateTime())->modify('-1 day'));

        $this->webClient->request('POST', '/api/authorize/check', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testAuthorizeCheckLogOut(): void
    {
        $this->webClient->request('POST', '/api/authorize/check');

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
