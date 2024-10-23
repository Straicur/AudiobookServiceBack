<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserSettingsController;

use App\Enums\UserEditType;
use App\Repository\UserEditRepository;
use App\Repository\UserPasswordRepository;
use App\Tests\AbstractWebTest;
use App\ValueGenerator\PasswordHashGenerator;
use DateTime;

class UserResetPasswordConfirmTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if password and editable flag has changed
     * @return void
     */
    public function test_userResetPasswordConfirmCorrect(): void
    {
        $userPasswordRepository = $this->getService(UserPasswordRepository::class);
        $userEditRepository = $this->getService(UserEditRepository::class);

        $this->assertInstanceOf(UserEditRepository::class, $userEditRepository);
        $this->assertInstanceOf(UserPasswordRepository::class, $userPasswordRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', edited: true, editableDate: (new DateTime())->modify('+1 month'));

        $userEdit = $this->databaseMockManager->testFunc_addUserEdit($user, false, UserEditType::PASSWORD_RESET, (new DateTime())->modify('+1 day'), true);

        $passwordGenerator = new PasswordHashGenerator('zaq12WSX');

        /// step 2
        $content = [
            'userId' => $user->getId(),
            'password' => 'zaq12WSX',
        ];

        $newPassword = $passwordGenerator->generate();

        /// step 3
        self::$webClient->request('PATCH', '/api/user/reset/password/confirm', content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
        /// step 5
        $password = $userPasswordRepository->findOneBy([
            'user' => $user->getId()
        ]);

        $userAfter = $password->getUser();
        $this->assertSame($newPassword, $password->getPassword());
        $this->assertTrue($userAfter->getEdited());

        $editAfter = $userEditRepository->findOneBy([
            'id' => $userEdit->getId()
        ]);

        $this->assertNotNull($editAfter);

        $this->assertTrue($editAfter->getEdited());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad UserId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_userResetPasswordConfirmIncorrectId(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', edited: true, editableDate: (new DateTime())->modify('+1 month'));

        $this->databaseMockManager->testFunc_addUserEdit($user, false, UserEditType::PASSWORD_RESET, (new DateTime())->modify('+1 day'), true);

        /// step 2
        $content = [
            'userId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
            'password' => 'zaq12WSX',
        ];
        /// step 3
        self::$webClient->request('PATCH', '/api/user/reset/password/confirm', content: json_encode($content));
        /// step 4
        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad user edit flag
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_userResetPasswordConfirmIncorrectUserEditFlag(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', editableDate: (new DateTime())->modify('-1 month'));

        $this->databaseMockManager->testFunc_addUserEdit($user, true, UserEditType::PASSWORD_RESET, (new DateTime())->modify('+1 day'), true);

        /// step 2
        $content = [
            'userId' => $user->getId(),
            'password' => 'zaq12WSX',
        ];
        /// step 3
        self::$webClient->request('PATCH', '/api/user/reset/password/confirm', content: json_encode($content));
        /// step 4
        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad EditableDate
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_userResetPasswordConfirmIncorrectUserEditableDate(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', edited: true, editableDate: (new DateTime())->modify('-1 month'));

        $this->databaseMockManager->testFunc_addUserEdit($user, false, UserEditType::PASSWORD_RESET, (new DateTime())->modify('-1 day'), true);

        /// step 2
        $content = [
            'userId' => $user->getId(),
            'password' => 'zaq12WSX',
        ];
        /// step 3
        self::$webClient->request('PATCH', '/api/user/reset/password/confirm', content: json_encode($content));
        /// step 4
        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function test_userResetPasswordConfirmEmptyRequestData(): void
    {
        $content = [];

        /// step 3
        self::$webClient->request('PATCH', '/api/user/reset/password/confirm', content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
