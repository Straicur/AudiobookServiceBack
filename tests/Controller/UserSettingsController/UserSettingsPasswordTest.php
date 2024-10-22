<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserSettingsController;

use App\Enums\UserEditType;
use App\Repository\UserPasswordRepository;
use App\Tests\AbstractWebTest;
use App\ValueGenerator\PasswordHashGenerator;
use DateTime;

/**
 * UserSettingsPasswordTest
 */
class UserSettingsPasswordTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if password has changed
     * @return void
     */
    public function test_userSettingsPasswordCorrect(): void
    {
        $userPasswordRepository = $this->getService(UserPasswordRepository::class);

        $this->assertInstanceOf(UserPasswordRepository::class, $userPasswordRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $userEdit1 = $this->databaseMockManager->testFunc_addUserEdit($user, false, UserEditType::PASSWORD, (new DateTime())->modify('+1 day'), true);

        $passwordGenerator2 = new PasswordHashGenerator('zaq12WSX');
        /// step 2
        $content = [
            'oldPassword' => 'zaq12wsx',
            'newPassword' => 'zaq12WSX',
            'code' => $userEdit1->getCode(),
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('PATCH', '/api/user/settings/password', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
        /// step 5
        $userPassword = $userPasswordRepository->findOneBy([
            'user' => $user->getId()
        ]);

        $this->assertSame($userPassword->getPassword(), $passwordGenerator2->generate());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad Password
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_userSettingsPasswordIncorrectPassword(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $userEdit1 = $this->databaseMockManager->testFunc_addUserEdit($user, false, UserEditType::PASSWORD, (new DateTime())->modify('+1 day'), true);

        /// step 2
        $content = [
            'oldPassword' => 'zaq12WSX',
            'newPassword' => 'zaq12Wsa',
            'code' => $userEdit1->getCode(),
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('PATCH', '/api/user/settings/password', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 4
        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad Password
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_userSettingsPasswordIncorrectCode(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $this->databaseMockManager->testFunc_addUserEdit($user, false, UserEditType::PASSWORD, (new DateTime())->modify('+1 day'), true);

        /// step 2
        $content = [
            'oldPassword' => 'zaq12wsx',
            'newPassword' => 'zaq12WSX',
            'code' => 'DS1D2211',
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 3
        self::$webClient->request('PATCH', '/api/user/settings/password', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 4
        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userSettingsPasswordEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        self::$webClient->request('PATCH', '/api/user/settings/password', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad permission
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userSettingsPasswordPermission(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');

        $content = [
            'oldPassword' => 'zaq12wsx',
            'newPassword' => 'zaq12WSX',
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        self::$webClient->request('PATCH', '/api/user/settings/password', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without token
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userSettingsPasswordLogOut(): void
    {
        $content = [
            'oldPassword' => 'zaq12wsx',
            'newPassword' => 'zaq12WSX',
        ];

        /// step 2
        self::$webClient->request('PATCH', '/api/user/settings/password', content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
