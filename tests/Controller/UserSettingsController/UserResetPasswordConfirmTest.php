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
    public function testUserResetPasswordConfirmCorrect(): void
    {
        $userPasswordRepository = $this->getService(UserPasswordRepository::class);
        $userEditRepository = $this->getService(UserEditRepository::class);

        $this->assertInstanceOf(UserEditRepository::class, $userEditRepository);
        $this->assertInstanceOf(UserPasswordRepository::class, $userPasswordRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', edited: true, editableDate: (new DateTime())->modify('+1 month'));

        $userEdit = $this->databaseMockManager->testFunc_addUserEdit($user, false, UserEditType::PASSWORD_RESET, (new DateTime())->modify('+1 day'), true);

        $passwordGenerator = new PasswordHashGenerator('zaq12WSX');

        $content = [
            'userId' => $user->getId(),
            'password' => 'zaq12WSX',
        ];

        $newPassword = $passwordGenerator->generate();

        $this->webClient->request('PATCH', '/api/user/reset/password/confirm', content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

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
     * Test checks bad given userId
     */
    public function testUserResetPasswordConfirmIncorrectId(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', edited: true, editableDate: (new DateTime())->modify('+1 month'));

        $this->databaseMockManager->testFunc_addUserEdit($user, false, UserEditType::PASSWORD_RESET, (new DateTime())->modify('+1 day'), true);

        $content = [
            'userId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
            'password' => 'zaq12WSX',
        ];

        $this->webClient->request('PATCH', '/api/user/reset/password/confirm', content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    /**
     * Test checks bad given userId(he has no edit flag in database)
     */
    public function testUserResetPasswordConfirmIncorrectUserEditFlag(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', editableDate: (new DateTime())->modify('-1 month'));

        $this->databaseMockManager->testFunc_addUserEdit($user, true, UserEditType::PASSWORD_RESET, (new DateTime())->modify('+1 day'), true);

        $content = [
            'userId' => $user->getId(),
            'password' => 'zaq12WSX',
        ];

        $this->webClient->request('PATCH', '/api/user/reset/password/confirm', content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    /**
     * Test checks bad given userId(His code expires)
     */
    public function testUserResetPasswordConfirmIncorrectUserEditableDate(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', edited: true, editableDate: (new DateTime())->modify('-1 month'));

        $this->databaseMockManager->testFunc_addUserEdit($user, false, UserEditType::PASSWORD_RESET, (new DateTime())->modify('-1 day'), true);

        $content = [
            'userId' => $user->getId(),
            'password' => 'zaq12WSX',
        ];

        $this->webClient->request('PATCH', '/api/user/reset/password/confirm', content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    public function testUserResetPasswordConfirmEmptyRequestData(): void
    {
        $content = [];

        $this->webClient->request('PATCH', '/api/user/reset/password/confirm', content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
