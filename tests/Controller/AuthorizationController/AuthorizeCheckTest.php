<?php

declare(strict_types=1);

namespace App\Tests\Controller\AuthorizationController;

use App\Tests\AbstractWebTest;
use DateTime;

/**
 * AuthorizeCheckTest
 */
class AuthorizeCheckTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response is correct
     * @return void
     */
    public function test_authorizeCheckCorrect(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@asuri.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');
        /// step 2
        $content = [
            'email' => 'test@asuri.pl',
            'password' => 'zaq12wsx'
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request('POST', '/api/authorize/check', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

    }

    /**
     * step 1 - Sending Request with expired token
     * step 2 - Checking response
     * @return void
     */
    public function test_authorizeCheckTokenExpired(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@asuri.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');
        /// step 2
        $content = [
            'email' => 'test@asuri.pl',
            'password' => 'zaq12wsx'
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user, (new DateTime())->modify('-1 day'));
        /// step 3
        $crawler = self::$webClient->request('POST', '/api/authorize/check', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 2
        self::assertResponseStatusCodeSame(401);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);
    }

    /**
     * step 1 - Sending Request without token
     * step 2 - Checking response
     * @return void
     */
    public function test_authorizeCheckLogOut(): void
    {
        /// step 1
        $crawler = self::$webClient->request('POST', '/api/authorize/check');
        /// step 2
        self::assertResponseStatusCodeSame(401);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);
    }
}
