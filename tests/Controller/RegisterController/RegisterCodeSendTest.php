<?php

declare(strict_types=1);

namespace App\Tests\Controller\RegisterController;

use App\Repository\RegisterCodeRepository;
use App\Tests\AbstractWebTest;

class RegisterCodeSendTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if user is registered
     * @return void
     */
    public function test_registerCodeCorrect(): void
    {
        $registerCodeRepository = $this->getService(RegisterCodeRepository::class);

        $this->assertInstanceOf(RegisterCodeRepository::class, $registerCodeRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx', notActive: true);

        $this->databaseMockManager->testFunc_addRegisterCode($user);
        /// step 2

        $content = [
            'email' => $user->getUserInformation()->getEmail(),
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('POST', '/api/register/code/send', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $this->assertCount(2, $registerCodeRepository->findAll());
    }

    /**
     * step 1 - Preparing JsonBodyContent with bad title
     * step 2 - Sending Request
     * step 3 - Checking response
     * @return void
     */
    public function test_registerCodeIncorrectActiveUserCredentials(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $this->databaseMockManager->testFunc_addRegisterCode($user);
        /// step 2

        $content = [
            'email' => 'test2@cos.pl',
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('POST', '/api/register/code/send', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing JsonBodyContent with bad title
     * step 2 - Sending Request
     * step 3 - Checking response
     * @return void
     */
    public function test_registerCodeIncorrectCredentials(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $this->databaseMockManager->testFunc_addRegisterCode($user);
        /// step 2

        $content = [
            'email' => 'test2@cos.pl',
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('POST', '/api/register/code/send', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function test_registerCodeEmptyRequest(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $this->databaseMockManager->testFunc_addRegisterCode($user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        self::$webClient->request('POST', '/api/register/code/send', server: [
            'HTTP_authorization' => $token->getToken()
        ]);
        /// step 2
        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
