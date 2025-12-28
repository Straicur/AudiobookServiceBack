<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserSettingsController;

use App\Enums\UserEditType;
use App\Repository\UserEditRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractWebTest;
use DateTime;

class UserResetPasswordTest extends AbstractWebTest
{
    public function testUserResetPasswordCorrect(): void
    {
        $userRepository = $this->getService(UserRepository::class);
        $userEditRepository = $this->getService(UserEditRepository::class);

        $this->assertInstanceOf(UserEditRepository::class, $userEditRepository);
        $this->assertInstanceOf(UserRepository::class, $userRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUserEdit($user, true, UserEditType::PASSWORD, (new DateTime())->modify('+1 day'));
        $this->databaseMockManager->testFunc_addUserEdit($user, false, UserEditType::PASSWORD, (new DateTime())->modify('+1 day'));

        $content = [
            'email' => $user->getUserInformation()->getEmail()
        ];

        $this->webClient->request('POST', '/api/user/reset/password', content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $userAfter = $userRepository->findOneBy([
            'id' => $user->getId()
        ]);

        $this->assertTrue($userAfter->getEdited());
        $this->assertNotNull($userAfter->getEditableDate());

        $this->assertCount(3, $userEditRepository->findAll());
        $this->assertCount(1, $userEditRepository->findBy([
            'edited' => false
        ]));
    }

    /**
     * Test checks bad given email
     */
    public function testUserResetPasswordIncorrectEmail(): void
    {
        $content = [
            'email' => 'test2@cos.pl'
        ];

        $this->webClient->request('POST', '/api/user/reset/password', content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    public function testUserResetPasswordEmptyRequestData(): void
    {
        $content = [];

        $this->webClient->request('POST', '/api/user/reset/password', content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
