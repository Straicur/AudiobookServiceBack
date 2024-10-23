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
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if all data change correctly
     * @return void
     */
    public function test_userResetPasswordCorrect(): void
    {
        $userRepository = $this->getService(UserRepository::class);
        $userEditRepository = $this->getService(UserEditRepository::class);

        $this->assertInstanceOf(UserEditRepository::class, $userEditRepository);
        $this->assertInstanceOf(UserRepository::class, $userRepository);
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUserEdit($user, true, UserEditType::PASSWORD, (new DateTime())->modify('+1 day'));
        $this->databaseMockManager->testFunc_addUserEdit($user, false, UserEditType::PASSWORD, (new DateTime())->modify('+1 day'));

        /// step 2
        $content = [
            'email' => $user->getUserInformation()->getEmail()
        ];

        /// step 3
        self::$webClient->request('POST', '/api/user/reset/password', content: json_encode($content));

        /// step 4
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
        /// step 5
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
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad Email
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_userResetPasswordIncorrectEmail(): void
    {

        /// step 2
        $content = [
            'email' => 'test2@cos.pl'
        ];


        /// step 3
        self::$webClient->request('POST', '/api/user/reset/password', content: json_encode($content));
        /// step 4
        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function test_userResetPasswordEmptyRequestData(): void
    {
        $content = [];

        /// step 2
        self::$webClient->request('POST', '/api/user/reset/password', content: json_encode($content));
        /// step 3
        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
