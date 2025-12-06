<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserSettingsController;

use App\Enums\UserEditType;
use App\Repository\UserEditRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractWebTest;
use DateTime;

class UserSettingsEmailTest extends AbstractWebTest
{
    public function testUserSettingsEmailCorrect(): void
    {
        $userRepository = $this->getService(UserRepository::class);
        $userEditRepository = $this->getService(UserEditRepository::class);

        $this->assertInstanceOf(UserEditRepository::class, $userEditRepository);
        $this->assertInstanceOf(UserRepository::class, $userRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $userEdit1 = $this->databaseMockManager->testFunc_addUserEdit($user, false, UserEditType::EMAIL_CODE, (new DateTime())->modify('+1 day'), true);

        $content = [
            'newEmail' => 'test2@cos.pl',
            'oldEmail' => $user->getUserInformation()->getEmail(),
            'code' => $userEdit1->getCode(),
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('POST', '/api/user/settings/email', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $userAfter = $userRepository->findOneBy([
            'id' => $user->getId()
        ]);

        $this->assertTrue($userAfter->getEdited());
        $this->assertNotNull($userAfter->getEditableDate());
        $this->assertCount(2, $userEditRepository->findAll());
    }

    /**
     * Test checks bad given email(User flag dont exists)
     */
    public function testUserSettingsEmailIncorrectEditExists(): void
    {
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');

        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $userEdit1 = $this->databaseMockManager->testFunc_addUserEdit($user2, false, UserEditType::EMAIL_CODE, (new DateTime())->modify('+1 day'), true);

        $content = [
            'newEmail' => 'test2@cos.pl',
            'oldEmail' => $user2->getUserInformation()->getEmail(),
            'code' => $userEdit1->getCode(),
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user2);

        $this->webClient->request('POST', '/api/user/settings/email', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    /**
     * Test checks bad given newEmail
     */
    public function testUserSettingsEmailIncorrectNewEmail(): void
    {
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');

        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $userEdit1 = $this->databaseMockManager->testFunc_addUserEdit($user2, false, UserEditType::EMAIL_CODE, (new DateTime())->modify('+1 day'), true);

        $content = [
            'newEmail' => 'test2@cos.pl',
            'oldEmail' => $user2->getUserInformation()->getEmail(),
            'code' => $userEdit1->getCode(),
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user2);

        $this->webClient->request('POST', '/api/user/settings/email', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    /**
     * Test checks bad given oldEmail
     */
    public function testUserSettingsEmailIncorrectOldEmail(): void
    {
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');

        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $userEdit1 = $this->databaseMockManager->testFunc_addUserEdit($user2, false, UserEditType::EMAIL_CODE, (new DateTime())->modify('+1 day'), true);

        $content = [
            'newEmail' => 'test2@cos.pl',
            'oldEmail' => 'test3@cos.pl',
            'code' => $userEdit1->getCode(),
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user2);

        $this->webClient->request('POST', '/api/user/settings/email', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));


        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    public function testUserSettingsEmailEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('POST', '/api/user/settings/email', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testUserSettingsEmailPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');

        $content = [
            'newEmail' => 'test2@cos.pl',
            'oldEmail' => $user->getUserInformation()->getEmail(),
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('POST', '/api/user/settings/email', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testUserSettingsEmailLogOut(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $content = [
            'newEmail' => 'test2@cos.pl',
            'oldEmail' => $user->getUserInformation()->getEmail(),
        ];

        $this->webClient->request('POST', '/api/user/settings/email', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
