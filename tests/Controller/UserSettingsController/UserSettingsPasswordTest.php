<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserSettingsController;

use App\Enums\UserEditType;
use App\Repository\UserPasswordRepository;
use App\Tests\AbstractWebTest;
use App\ValueGenerator\PasswordHashGenerator;
use DateTime;

class UserSettingsPasswordTest extends AbstractWebTest
{
    public function testUserSettingsPasswordCorrect(): void
    {
        $userPasswordRepository = $this->getService(UserPasswordRepository::class);

        $this->assertInstanceOf(UserPasswordRepository::class, $userPasswordRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $userEdit1 = $this->databaseMockManager->testFunc_addUserEdit($user, false, UserEditType::PASSWORD, (new DateTime())->modify('+1 day'), true);

        $passwordGenerator2 = new PasswordHashGenerator('zaq12WSX');

        $content = [
            'oldPassword' => 'zaq12wsx',
            'newPassword' => 'zaq12WSX',
            'code' => $userEdit1->getCode(),
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/user/settings/password', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $userPassword = $userPasswordRepository->findOneBy([
            'user' => $user->getId()
        ]);

        $this->assertSame($userPassword->getPassword(), $passwordGenerator2->generate());
    }

    /**
     * Test checks bad given oldPassword
     */
    public function testUserSettingsPasswordIncorrectPassword(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $userEdit1 = $this->databaseMockManager->testFunc_addUserEdit($user, false, UserEditType::PASSWORD, (new DateTime())->modify('+1 day'), true);

        $content = [
            'oldPassword' => 'zaq12WSX',
            'newPassword' => 'zaq12Wsa',
            'code' => $userEdit1->getCode(),
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/user/settings/password', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    /**
     * Test checks bad given code
     */
    public function testUserSettingsPasswordIncorrectCode(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUserEdit($user, false, UserEditType::PASSWORD, (new DateTime())->modify('+1 day'), true);

        $content = [
            'oldPassword' => 'zaq12wsx',
            'newPassword' => 'zaq12WSX',
            'code' => 'DS1D2211',
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/user/settings/password', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    public function testUserSettingsPasswordEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/user/settings/password', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testUserSettingsPasswordPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');

        $content = [
            'oldPassword' => 'zaq12wsx',
            'newPassword' => 'zaq12WSX',
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/user/settings/password', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testUserSettingsPasswordLogOut(): void
    {
        $content = [
            'oldPassword' => 'zaq12wsx',
            'newPassword' => 'zaq12WSX',
        ];

        $this->webClient->request('PATCH', '/api/user/settings/password', content: json_encode($content));
        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
