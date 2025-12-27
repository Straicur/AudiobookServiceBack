<?php

declare(strict_types=1);

namespace App\Tests\Controller\UserSettingsController;

use App\Repository\UserRepository;
use App\Tests\AbstractWebTest;
use DateTime;

class UserParentControlPatchTest extends AbstractWebTest
{
    public function testUserParentControlPatchCorrect(): void
    {
        $userRepository = $this->getService(UserRepository::class);

        $this->assertInstanceOf(UserRepository::class, $userRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $userParentalControlCode = $this->databaseMockManager->testFunc_addUserParentalControlCode($user);

        $content = [
            'smsCode' => $userParentalControlCode->getCode(),
            'additionalData' => [
            ],
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/user/parent/control', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $userAfter = $userRepository->findOneBy([
            'id' => $user->getId()
        ]);

        $this->assertNull($userAfter->getUserInformation()->getBirthday());
    }

    public function testUserParentControlPatchBirthdayCorrect(): void
    {
        $userRepository = $this->getService(UserRepository::class);

        $this->assertInstanceOf(UserRepository::class, $userRepository);

        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $userParentalControlCode = $this->databaseMockManager->testFunc_addUserParentalControlCode($user);

        $content = [
            'smsCode' => $userParentalControlCode->getCode(),
            'additionalData' => [
                'birthday' => '01.09.1998',
            ],
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/user/parent/control', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $birthdayDate = DateTime::createFromFormat('d.m.Y', '01.09.1998');

        $userAfter = $userRepository->findOneBy([
            'id' => $user->getId()
        ]);

        $this->assertSame($userAfter->getUserInformation()->getBirthday()->getTimestamp(), $birthdayDate->getTimestamp());
    }

    /**
     * Test checks bad given smsCode
     */
    public function testUserSettingsIncorrectSmsCode(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123121', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');

        $content = [
            'smsCode' => 'A2312V4',
            'additionalData' => [
                'birthday' => '01.09.1998',
            ],
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/user/parent/control', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData($this->webClient);
    }

    public function testUserParentControlPatchEmptyRequestData(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/user/parent/control', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testUserParentControlPatchPermission(): void
    {
        $user = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest'], true, 'zaq12wsx');

        $content = [
            'smsCode' => 'A2312V4',
            'additionalData' => [
                'birthday' => '01.09.1998',
            ],
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user);

        $this->webClient->request('PATCH', '/api/user/parent/control', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData($this->webClient);
    }

    public function testUserParentControlPatchLogOut(): void
    {
        $content = [
            'smsCode' => 'A2312V4',
            'additionalData' => [
                'birthday' => '01.09.1998',
            ],
        ];

        $this->webClient->request('PATCH', '/api/user/parent/control', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData($this->webClient);
    }
}
