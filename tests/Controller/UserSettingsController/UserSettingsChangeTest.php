<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserSettingsController;

use App\Enums\UserEditType;
use App\Repository\UserRepository;
use App\Tests\AbstractWebTest;
use DateTime;

class UserSettingsChangeTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if all data has changed
     * @return void
     */
    public function test_userSettingsChangeCorrect(): void
    {
        $userRepository = $this->getService(UserRepository::class);

        $this->assertInstanceOf(UserRepository::class, $userRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $userEdit1 = $this->databaseMockManager->testFunc_addUserEdit($user, false, UserEditType::USER_DATA, (new DateTime())->modify('+1 day'), true);

        $content = [
            'phoneNumber' => '+48124124124',
            'firstName' => 'Damian',
            'lastName' => 'Mos',
            'code' => $userEdit1->getCode(),
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('PATCH', '/api/user/settings/change', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $userAfter = $userRepository->findOneBy([
            'id' => $user->getId()
        ]);

        $this->assertNotSame('+48123123123', $userAfter->getUserInformation()->getPhoneNumber());
        $this->assertNotSame('Test', $userAfter->getUserInformation()->getLastname());
        $this->assertNotSame('User', $userAfter->getUserInformation()->getFirstname());
    }

    /**
     * /**
     *  step 1 - Preparing data
     *  step 2 - Preparing JsonBodyContent with bad PhoneNumber
     *  step 3 - Sending Request
     *  step 4 - Checking response
     *
     * @return void
     */
    public function test_userSettingsIncorrectPhoneNumber(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123121', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');

        $userEdit1 = $this->databaseMockManager->testFunc_addUserEdit($user, false, UserEditType::USER_DATA, (new DateTime())->modify('+1 day'), true);

        $content = [
            'phoneNumber' => '+48123123121',
            'firstName' => 'Damian',
            'lastName' => 'Mos',
            'code' => $userEdit1->getCode(),
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('PATCH', '/api/user/settings/change', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }
    /**
     * /**
     *  step 1 - Preparing data
     *  step 2 - Preparing JsonBodyContent with bad PhoneNumber
     *  step 3 - Sending Request
     *  step 4 - Checking response
     *
     * @return void
     */
    public function test_userSettingsIncorrectCode(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123121', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');

        $this->databaseMockManager->testFunc_addUserEdit($user, false, UserEditType::USER_DATA, (new DateTime())->modify('+1 day'), true);

        $content = [
            'phoneNumber' => '+48124124124',
            'firstName' => 'Damian',
            'lastName' => 'Mos',
            'code' => 'DS1D2211',
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('PATCH', '/api/user/settings/change', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function test_userSettingsChangeEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('PATCH', '/api/user/settings/change', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_userSettingsChangePermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');

        $content = [
            'phoneNumber' => '+48124124124',
            'firstName' => 'Damian',
            'lastName' => 'Mos',
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        self::$webClient->request('PATCH', '/api/user/settings/change', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_userSettingsChangeLogOut(): void
    {
        $content = [
            'phoneNumber' => '+48124124124',
            'firstName' => 'Damian',
            'lastName' => 'Mos',
        ];

        self::$webClient->request('PATCH', '/api/user/settings/change', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
