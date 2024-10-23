<?php

declare(strict_types=1);

namespace App\Tests\Controller\AuthorizationController;

use App\Tests\AbstractWebTest;

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
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@audiobookback.icu', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [
            'email' => 'test@audiobookback.icu',
            'password' => 'zaq12wsx'
        ];

        self::$webClient->request('POST', '/api/authorize', content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

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
        $content = [
            'email' => 'tester@audiobookback.icu',
            'password' => 'zaq12wsx'
        ];

        self::$webClient->request('POST', '/api/authorize', content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function test_loginEmptyRequest(): void
    {
        self::$webClient->request('POST', '/api/authorize');

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
