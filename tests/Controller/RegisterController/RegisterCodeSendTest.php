<?php

declare(strict_types=1);

namespace App\Tests\Controller\RegisterController;

use App\Repository\RegisterCodeRepository;
use App\Tests\AbstractWebTest;

class RegisterCodeSendTest extends AbstractWebTest
{
    public function testRegisterCodeCorrect(): void
    {
        $registerCodeRepository = $this->getService(RegisterCodeRepository::class);

        $this->assertInstanceOf(RegisterCodeRepository::class, $registerCodeRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx', notActive: true);

        $this->databaseMockManager->testFunc_addRegisterCode($user);

        $content = [
            'email' => $user->getUserInformation()->getEmail(),
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('POST', '/api/register/code/send', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $this->assertCount(2, $registerCodeRepository->findAll());
    }

    /**
     * Test checks bad given email(user with this email don't exist)
     */
    public function testRegisterCodeIncorrectActiveUserCredentials(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $this->databaseMockManager->testFunc_addRegisterCode($user);

        $content = [
            'email' => 'test2@cos.pl',
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('POST', '/api/register/code/send', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * Test checks bad given email(user is active)
     */
    public function testRegisterCodeIncorrectCredentials(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $this->databaseMockManager->testFunc_addRegisterCode($user);

        $content = [
            'email' => 'test@cos.pl',
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('POST', '/api/register/code/send', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function testRegisterCodeEmptyRequest(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $this->databaseMockManager->testFunc_addRegisterCode($user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('POST', '/api/register/code/send', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
