<?php

declare(strict_types=1);

namespace App\Tests\Controller\RegisterController;

use App\Repository\RegisterCodeRepository;
use App\Repository\UserInformationRepository;
use App\Tests\AbstractWebTest;

/**
 * RegisterTest
 */
class RegisterTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if user is registered
     * @return void
     */
    public function test_registerCorrect(): void
    {
        $userInformationRepository = $this->getService(UserInformationRepository::class);
        $registerCodeRepository = $this->getService(RegisterCodeRepository::class);

        $this->assertInstanceOf(RegisterCodeRepository::class, $registerCodeRepository);
        $this->assertInstanceOf(UserInformationRepository::class, $userInformationRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');
        /// step 2

        $content = [
            'email' => 'mosinskidamian17@gmail.com',
            'phoneNumber' => '786768564',
            'firstname' => 'Damian',
            'lastname' => 'Mos',
            'password' => 'zaq12wsx'
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request('PUT', '/api/register', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        /// step 5
        $userAfter = $userInformationRepository->findOneBy([
            'email' => 'mosinskidamian17@gmail.com'
        ])->getUser();

        $this->assertNotNull($userAfter);

        $hasRole = false;

        foreach ($userAfter->getRoles() as $role) {
            if ($role->getName() === 'Guest') {
                $hasRole = true;
            }
        }

        $this->assertTrue($hasRole);
        $this->assertFalse($userAfter->isActive());
        $this->assertCount(1, $registerCodeRepository->findAll());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if user is registered
     * @return void
     */
    public function test_registerParentalControlCorrect(): void
    {
        $userInformationRepository = $this->getService(UserInformationRepository::class);
        $registerCodeRepository = $this->getService(RegisterCodeRepository::class);

        $this->assertInstanceOf(RegisterCodeRepository::class, $registerCodeRepository);
        $this->assertInstanceOf(UserInformationRepository::class, $userInformationRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');
        /// step 2

        $content = [
            'email' => 'mosinskidamian17@gmail.com',
            'phoneNumber' => '786768564',
            'firstname' => 'Damian',
            'lastname' => 'Mos',
            'password' => 'zaq12wsx',
            'additionalData' => [
                'birthday' => '10.09.2005'
            ]
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        $crawler = self::$webClient->request('PUT', '/api/register', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);

        /// step 5
        $userInformationAfter = $userInformationRepository->findOneBy([
            'email' => 'mosinskidamian17@gmail.com'
        ]);

        $this->assertNotNull($userInformationAfter->getUser());

        $hasRole = false;

        foreach ($userInformationAfter->getUser()->getRoles() as $role) {
            if ($role->getName() === 'Guest') {
                $hasRole = true;
            }
        }

        $this->assertTrue($hasRole);
        $this->assertFalse($userInformationAfter->getUser()->isActive());
        $this->assertCount(1, $registerCodeRepository->findAll());
        $this->assertNotNull($userInformationAfter->getBirthday());
    }

    /**
     * step 1 - Preparing JsonBodyContent with bad email
     * step 2 - Sending Request
     * step 3 - Checking response
     * @return void
     */
    public function test_registerIncorrectEmailCredentials(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');
        /// step 1
        $content = [
            'email' => 'test@cos.pl',
            'phoneNumber' => '786768564',
            'firstname' => 'Damian',
            'lastname' => 'Mos',
            'password' => 'zaq12wsx'
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $crawler = self::$webClient->request('PUT', '/api/register', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(404);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('error', $responseContent);
        $this->assertArrayHasKey('data', $responseContent);
    }

    /**
     * step 1 - Preparing JsonBodyContent with bad number
     * step 2 - Sending Request
     * step 3 - Checking response
     * @return void
     */
    public function test_registerIncorrectNumberCredentials(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');
        /// step 1
        $content = [
            'email' => 'test@cos.pl',
            'phoneNumber' => '+48123123123',
            'firstname' => 'Damian',
            'lastname' => 'Mos',
            'password' => 'zaq12wsx'
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $crawler = self::$webClient->request('PUT', '/api/register', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(404);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('error', $responseContent);
        $this->assertArrayHasKey('data', $responseContent);
    }

    /**
     * step 1 - Preparing JsonBodyContent with bad institution max users
     * step 2 - Sending Request
     * step 3 - Checking response
     * @return void
     */
    public function test_registerIncorrectInstitutionCredentials(): void
    {
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@1cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@2cos.pl', '+48123123124', ['Guest', 'User'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@3cos.pl', '+48123123125', ['Guest', 'User'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@4cos.pl', '+48123123126', ['Guest', 'User'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@5cos.pl', '+48123123127', ['Guest', 'User'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@6cos.pl', '+48123123128', ['Guest', 'User'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@7cos.pl', '+48123123129', ['Guest', 'User'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@8cos.pl', '+48123123120', ['Guest', 'User'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@9cos.pl', '+48123123223', ['Guest', 'User'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@10cos.pl', '+48153123223', ['Guest', 'User'], true, 'zaq12wsx');
        /// step 1

        $content = [
            'email' => 'test2@cos.pl',
            'phoneNumber' => '786768564',
            'firstname' => 'Damian',
            'lastname' => 'Mos',
            'password' => 'zaq12wsx'
        ];

        /// step 2
        $crawler = self::$webClient->request('PUT', '/api/register', content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(404);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('error', $responseContent);
        $this->assertArrayHasKey('data', $responseContent);
    }

    /**
     * step 1 - Sending Request without content
     * step 2 - Checking response
     * @return void
     */
    public function test_registerEmptyRequest(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        /// step 1
        $crawler = self::$webClient->request('PUT', '/api/register', server: [
            'HTTP_authorization' => $token->getToken()
        ]);
        /// step 2
        self::assertResponseStatusCodeSame(400);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);
    }
}