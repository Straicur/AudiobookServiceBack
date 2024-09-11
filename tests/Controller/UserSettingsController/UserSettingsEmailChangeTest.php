<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserSettingsController;

use App\Enums\UserEditType;
use App\Repository\UserEditRepository;
use App\Repository\UserRepository;
use App\Tests\AbstractWebTest;
use DateTime;

/**
 * UserSettingsEmailChangeChangeTest
 */
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
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', edited: true, editableDate: (new DateTime())->modify('+1 month'));

        $userEdit = $this->databaseMockManager->testFunc_addUserEdit($user, false, UserEditType::EMAIL, (new DateTime())->modify('+1 day'));

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $crawler = self::$webClient->request('GET', '/api/user/settings/email/change/test2@cos.pl/' . $user->getId()->__toString());

        /// step 3
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $userAfter = $userRepository->findOneBy([
            'id' => $user->getId()
        ]);
        /// step 4
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
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', edited: true, editableDate: (new DateTime())->modify('-1 month'));

        $this->databaseMockManager->testFunc_addUserEdit($user, false, UserEditType::EMAIL, (new DateTime())->modify('-1 day'));

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $crawler = self::$webClient->request('GET', '/api/user/settings/email/change/test2@cos.pl/' . $user->getId()->__toString());
        /// step 3
        self::assertResponseStatusCodeSame(404);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('error', $responseContent);
        $this->assertArrayHasKey('data', $responseContent);
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
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', editableDate: (new DateTime())->modify('-1 month'));

        $this->databaseMockManager->testFunc_addUserEdit($user, true, UserEditType::EMAIL, (new DateTime())->modify('-1 day'));

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $crawler = self::$webClient->request('GET', '/api/user/settings/email/change/test2@cos.pl/' . $user->getId()->__toString());
        /// step 3
        self::assertResponseStatusCodeSame(404);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('error', $responseContent);
        $this->assertArrayHasKey('data', $responseContent);
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
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', edited: true, editableDate: (new DateTime())->modify('+1 month'));

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $crawler = self::$webClient->request('GET', '/api/user/settings/email/change/test2@cos.pl/66666c4e-16e6-1ecc-9890-a7e8b0073d3b');
        /// step 3
        self::assertResponseStatusCodeSame(404);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('error', $responseContent);
        $this->assertArrayHasKey('data', $responseContent);
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
        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', edited: true, editableDate: (new DateTime())->modify('+1 month'));

        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123128', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user2);
        /// step 2
        $crawler = self::$webClient->request('GET', '/api/user/settings/email/change/test2@cos.pl/' . $user2->getId()->__toString());
        /// step 3
        self::assertResponseStatusCodeSame(404);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('error', $responseContent);
        $this->assertArrayHasKey('data', $responseContent);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Sending Request without content
     * step 3 - Checking response
     *
     * @return void
     */
    public function test_userSettingsEmailChangeEmptyRequestData(): void
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', edited: true, editableDate: (new DateTime())->modify('+1 month'));

        $token = $this->databaseMockManager->testFunc_loginUser($user);
        /// step 2
        $crawler = self::$webClient->request('GET', '/api/user/settings/email/change//');
        /// step 3
        self::assertResponseStatusCodeSame(404);

        $responseContent = self::$webClient->getResponse()->getContent();

        $this->assertNotNull($responseContent);
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseContent = json_decode($responseContent, true);

        $this->assertIsArray($responseContent);
        $this->assertArrayHasKey('error', $responseContent);
        $this->assertArrayHasKey('data', $responseContent);
    }

}