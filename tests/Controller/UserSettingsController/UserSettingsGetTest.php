<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserSettingsController;

use App\Tests\AbstractWebTest;

class UserSettingsGetTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if all data has changed
     * @return void
     */
    public function testUserSettingsGetCorrect(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('GET', '/api/user/settings', server: [
            'HTTP_authorization' => $token->getToken()
        ]);


        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('phoneNumber', $responseContent);
        $this->assertSame($user->getUserInformation()->getPhoneNumber(), $responseContent['phoneNumber']);
        $this->assertArrayHasKey('firstname', $responseContent);
        $this->assertSame($user->getUserInformation()->getFirstname(), $responseContent['firstname']);
        $this->assertArrayHasKey('lastname', $responseContent);
        $this->assertSame($user->getUserInformation()->getLastname(), $responseContent['lastname']);
        $this->assertArrayHasKey('email', $responseContent);
        $this->assertSame($user->getUserInformation()->getEmail(), $responseContent['email']);
        $this->assertArrayHasKey('edited', $responseContent);
        $this->assertSame($user->getEdited(), $responseContent['edited']);
        $this->assertArrayHasKey('editableDate', $responseContent);
    }

    public function testUserSettingsGetPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('GET', '/api/user/settings', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function testUserSettingsGetLogOut(): void
    {
        self::$webClient->request('GET', '/api/user/settings');

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
