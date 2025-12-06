<?php

declare(strict_types=1);

namespace App\Tests\Controller\AuthorizationController;

use App\Tests\AbstractWebTest;

class LoginTest extends AbstractWebTest
{
    public function testLoginCorrect(): void
    {
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@audiobookback.icu', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [
            'email' => 'test@audiobookback.icu',
            'password' => 'zaq12wsx'
        ];

        $this->webClient->request('POST', '/api/authorize', content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = $this->webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('token', $responseContent);
        $this->assertArrayHasKey('roles', $responseContent);
    }

    /**
     * Test checks bad given email(user with this email don't existS)
     */
    public function testLoginIncorrectCredentials(): void
    {
        $content = [
            'email' => 'tester@audiobookback.icu',
            'password' => 'zaq12wsx'
        ];

        $this->webClient->request('POST', '/api/authorize', content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    public function testLoginEmptyRequest(): void
    {
        $this->webClient->request('POST', '/api/authorize');

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
