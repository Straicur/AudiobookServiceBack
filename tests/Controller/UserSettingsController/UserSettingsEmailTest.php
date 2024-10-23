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
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if all data change correctly
     * @return void
     */
    public function test_userSettingsEmailCorrect(): void
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

        self::$webClient->request('POST', '/api/user/settings/email', server: [
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
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with existing Edit
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_userSettingsEmailIncorrectEditExists(): void
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

        self::$webClient->request('POST', '/api/user/settings/email', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad NewEmail
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_userSettingsEmailIncorrectNewEmail(): void
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

        self::$webClient->request('POST', '/api/user/settings/email', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad OldEmail
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_userSettingsEmailIncorrectOldEmail(): void
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

        self::$webClient->request('POST', '/api/user/settings/email', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));


        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function test_userSettingsEmailEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('POST', '/api/user/settings/email', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_userSettingsEmailPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');

        $content = [
            'newEmail' => 'test2@cos.pl',
            'oldEmail' => $user->getUserInformation()->getEmail(),
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('POST', '/api/user/settings/email', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_userSettingsEmailLogOut(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $content = [
            'newEmail' => 'test2@cos.pl',
            'oldEmail' => $user->getUserInformation()->getEmail(),
        ];

        self::$webClient->request('POST', '/api/user/settings/email', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
