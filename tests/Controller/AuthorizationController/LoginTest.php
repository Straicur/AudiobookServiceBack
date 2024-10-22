<?php

declare(strict_types=1);

namespace App\Tests\Controller\AuthorizationController;

use App\Tests\AbstractWebTest;

/**
 * LoginTest
 */
class LoginTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if responseContent has key token
     * @return void
     */
    public function test_loginCorrect(): void
    {
        /// step 1
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@audiobookback.icu', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');
        /// step 2
        $content = [
            'email' => 'test@audiobookback.icu',
            'password' => 'zaq12wsx'
        ];
        /// step 3
        self::$webClient->request('POST', '/api/authorize', content: json_encode($content));
        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);
        /// step 5
        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('token', $responseContent);
        $this->assertArrayHasKey('roles', $responseContent);
    }

    /**
     * step 1 - Preparing JsonBodyContent where there is no email tester@audiobookback.icu
     * step 2 - Sending Request
     * step 3 - Checking response
     * @return void
     */
    public function test_loginIncorrectCredentials(): void
    {
        /// step 1
        $content = [
            'email' => 'tester@audiobookback.icu',
            'password' => 'zaq12wsx'
        ];
        /// step 2
        self::$webClient->request('POST', '/api/authorize', content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * step 1 - Sending Request without content
     * step 2 - Checking response
     * @return void
     */
    public function test_loginEmptyRequest(): void
    {
        /// step 1
        self::$webClient->request('POST', '/api/authorize');
        /// step 2
        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
