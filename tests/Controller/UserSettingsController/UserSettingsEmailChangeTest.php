<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserSettingsController;

use App\Enums\UserEditType;
use App\Repository\UserEditRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractWebTest;
use DateTime;

class UserSettingsEmailChangeTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Checking response
     * step 4 - Checking response if all data has changed
     * @return void
     */
    public function test_userSettingsEmailChangeCorrect(): void
    {
        $userRepository = $this->getService(UserRepository::class);
        $userEditRepository = $this->getService(UserEditRepository::class);

        $this->assertInstanceOf(UserEditRepository::class, $userEditRepository);
        $this->assertInstanceOf(UserRepository::class, $userRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', edited: true, editableDate: (new DateTime())->modify('+1 month'));

        $userEdit = $this->databaseMockManager->testFunc_addUserEdit($user, false, UserEditType::EMAIL, (new DateTime())->modify('+1 day'));

        self::$webClient->request('GET', '/api/user/settings/email/change/test2@cos.pl/' . $user->getId()->__toString());

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $userAfter = $userRepository->findOneBy([
            'id' => $user->getId()
        ]);

        $this->assertSame($userAfter->getUserInformation()->getEmail(), 'test2@cos.pl');
        $this->assertTrue($userAfter->getEdited());

        $editAfter = $userEditRepository->findOneBy([
            'id' => $userEdit->getId()
        ]);

        $this->assertNotNull($editAfter);

        $this->assertTrue($editAfter->getEdited());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad Editable date
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userSettingsEmailChangeIncorrectEditableDate(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', edited: true, editableDate: (new DateTime())->modify('-1 month'));

        $this->databaseMockManager->testFunc_addUserEdit($user, false, UserEditType::EMAIL, (new DateTime())->modify('-1 day'));

        self::$webClient->request('GET', '/api/user/settings/email/change/test2@cos.pl/' . $user->getId()->__toString());

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad EditFlag
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userSettingsEmailChangeIncorrectEditFlag(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', editableDate: (new DateTime())->modify('-1 month'));

        $this->databaseMockManager->testFunc_addUserEdit($user, true, UserEditType::EMAIL, (new DateTime())->modify('-1 day'));

        self::$webClient->request('GET', '/api/user/settings/email/change/test2@cos.pl/' . $user->getId()->__toString());

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad CategoryKey
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userSettingsEmailChangeIncorrectUserId(): void
    {
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx', edited: true, editableDate: (new DateTime())->modify('+1 month'));

        self::$webClient->request('GET', '/api/user/settings/email/change/test2@cos.pl/66666c4e-16e6-1ecc-9890-a7e8b0073d3b');

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request with bad CategoryKey
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userSettingsEmailChangeIncorrectEmail(): void
    {
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx', edited: true, editableDate: (new DateTime())->modify('+1 month'));

        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123128', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        self::$webClient->request('GET', '/api/user/settings/email/change/test2@cos.pl/' . $user2->getId()->__toString());

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function test_userSettingsEmailChangeEmptyRequestData(): void
    {
        self::$webClient->request('GET', '/api/user/settings/email/change//');

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }
}
