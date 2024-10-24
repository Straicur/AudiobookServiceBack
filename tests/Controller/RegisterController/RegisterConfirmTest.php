<?php

declare(strict_types=1);

namespace App\Tests\Controller\RegisterController;

use App\Repository\RegisterCodeRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractWebTest;
use DateTime;

class RegisterConfirmTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if user is active
     * @return void
     */
    public function testRegisterConfirmCorrect(): void
    {
        $userRepository = $this->getService(UserRepository::class);
        $registerCodeRepository = $this->getService(RegisterCodeRepository::class);

        $this->assertInstanceOf(RegisterCodeRepository::class, $registerCodeRepository);
        $this->assertInstanceOf(UserRepository::class, $userRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');
        $registerCode = $this->databaseMockManager->testFunc_addRegisterCode($user, code: '95b7tjxrnbs88xd');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('GET', '/api/register/' . $user->getUserInformation()->getEmail() . '/95b7tjxrnbs88xd', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $userAfter = $userRepository->findOneBy([
            'id' => $user->getId()
        ]);

        $hasRole = false;

        foreach ($userAfter->getRoles() as $role) {
            if ($role->getName() === 'User') {
                $hasRole = true;
            }
        }

        $this->assertTrue($hasRole);
        $this->assertTrue($userAfter->isActive());

        $codeAfter = $registerCodeRepository->findOneBy([
            'id' => $registerCode->getId()
        ]);
        $this->assertNotNull($codeAfter->getDateAccept());
        $this->assertFalse($codeAfter->getActive());
    }

    /**
     * step 1 - Preparing JsonBodyContent with bad registerCode
     * step 2 - Sending Request
     * step 3 - Checking response
     * @return void
     */
    public function testRegisterConfirmIncorrectCredentials(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addRegisterCode($user, code: '95b7tjxrnbs88xd', active: true, dateAccept: new DateTime());

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('GET', '/api/register/' . $user->getUserInformation()->getEmail() . '/95b7tjxrnbs88xd', server: [
            'HTTP_authorization' => $token->getToken()
        ]);         
        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing JsonBodyContent with bad registerCode status
     * step 2 - Sending Request
     * step 3 - Checking response
     * @return void
     */
    public function testRegisterConfirmIncorrectCodeStatusCredentials(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addRegisterCode($user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('GET', '/api/register/' . $user->getUserInformation()->getEmail() . '/UPqFDj', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing JsonBodyContent with bad registerCode
     * step 2 - Sending Request
     * step 3 - Checking response
     * @return void
     */
    public function testRegisterConfirmIncorrectEmailCredentials(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addRegisterCode($user, code: '95b7tjxrnbs88xd');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('GET', '/api/register/test2@cos.pl/UPqFDj', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * step 1 - Sending Request without content
     * step 2 - Checking response
     * @return void
     */
    public function testRegisterConfirmEmptyRequest(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addRegisterCode($user);

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('GET', '/api/register/', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }
}
