<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminUserNotificationsController;

use App\Enums\AudiobookAgeRange;
use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Repository\NotificationRepository;
use App\Tests\AbstractWebTest;
use DateTime;

class AdminUserNotificationPatchTest extends AbstractWebTest
{
    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if notification was added
     * @return void
     */
    public function test_adminUserNotificationPatchCorrect(): void
    {
        $notificationRepository = $this->getService(NotificationRepository::class);

        $this->assertInstanceOf(NotificationRepository::class, $notificationRepository);

        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest'], true, 'zaq12wsx', notActive: true);
        $user3 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123126', ['Guest', 'User'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category2, true);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd2', [$category2], active: true);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category2], active: true);

        $audiobook4 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd4', [$category4, $category2], active: true);
        $audiobook5 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd5', [$category5], active: true);
        $audiobook6 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd6', [$category5], active: true);

        $this->databaseMockManager->testFunc_addProposedAudiobooks($user1, $audiobook1);
        $this->databaseMockManager->testFunc_addProposedAudiobooks($user1, $audiobook2);
        $this->databaseMockManager->testFunc_addProposedAudiobooks($user1, $audiobook3);
        $this->databaseMockManager->testFunc_addProposedAudiobooks($user1, $audiobook4);
        $this->databaseMockManager->testFunc_addProposedAudiobooks($user1, $audiobook5);
        $this->databaseMockManager->testFunc_addProposedAudiobooks($user1, $audiobook6);

        $not1 = $this->databaseMockManager->testFunc_addNotifications([$user1, $user2, $user3], NotificationType::ADMIN, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);

        $content = [
            'notificationId' => $not1->getId(),
            'notificationType' => NotificationType::PROPOSED->value,
            'notificationUserType' => NotificationUserType::SYSTEM->value,
            'additionalData' => [
                'text' => 'Nowy text',
                'actionId' => $user1->getProposedAudiobooks()->getId()
            ]
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        self::$webClient->request('PATCH', '/api/admin/user/notification', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $not1After = $notificationRepository->findOneBy([
            'id' => $not1->getId()
        ]);

        $this->assertSame($content['notificationType'], $not1After->getType()->value);
        $this->assertSame($content['additionalData']['actionId']->toBinary(), $not1After->getActionId()->toBinary());

        $metaData = $not1After->getMetaData();

        $this->assertSame($metaData['user'], $content['notificationUserType']);
        $this->assertSame($metaData['text'], $content['additionalData']['text']);

        $this->assertCount(6, $not1After->getUsers());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent
     * step 3 - Sending Request
     * step 4 - Checking response
     * step 5 - Checking response if notification was added
     * @return void
     */
    public function test_adminUserNotificationPatchToAdminCorrect(): void
    {
        $notificationRepository = $this->getService(NotificationRepository::class);

        $this->assertInstanceOf(NotificationRepository::class, $notificationRepository);

        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest'], true, 'zaq12wsx', notActive: true);
        $user3 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123126', ['Guest', 'User'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category2, true);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1,
            $category2], active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd2', [$category2], active: true);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category2], active: true);

        $audiobook4 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd4', [$category4,
            $category2], active: true);
        $audiobook5 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd5', [$category5], active: true);
        $audiobook6 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd6', [$category5], active: true);

        $this->databaseMockManager->testFunc_addProposedAudiobooks($user1, $audiobook1);
        $this->databaseMockManager->testFunc_addProposedAudiobooks($user1, $audiobook2);
        $this->databaseMockManager->testFunc_addProposedAudiobooks($user1, $audiobook3);
        $this->databaseMockManager->testFunc_addProposedAudiobooks($user1, $audiobook4);
        $this->databaseMockManager->testFunc_addProposedAudiobooks($user1, $audiobook5);
        $this->databaseMockManager->testFunc_addProposedAudiobooks($user1, $audiobook6);

        $not1 = $this->databaseMockManager->testFunc_addNotifications([$user1,
            $user2,
            $user3], NotificationType::PROPOSED, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);

        $content = [
            'notificationId'       => $not1->getId(),
            'notificationType'     => NotificationType::ADMIN->value,
            'notificationUserType' => NotificationUserType::SYSTEM->value,
            'additionalData'       => [
                'text'     => 'Nowy text',
                'actionId' => $user1->getId(),
            ],
        ];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        self::$webClient->request('PATCH', '/api/admin/user/notification', server: [
            'HTTP_authorization' => $token->getToken(),
        ], content: json_encode($content));

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $not1After = $notificationRepository->findOneBy([
            'id' => $not1->getId(),
        ]);

        $this->assertSame($content['notificationType'], $not1After->getType()->value);
        $this->assertSame($content['additionalData']['actionId']->toBinary(), $not1After->getActionId()->toBinary());

        $metaData = $not1After->getMetaData();

        $this->assertSame($metaData['user'], $content['notificationUserType']);
        $this->assertSame($metaData['text'], $content['additionalData']['text']);

        $this->assertCount(1, $not1After->getUsers());
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad notificationId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminUserNotificationPatchIncorrectNotificationId(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123128', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123126', ['Guest', 'User'], true, 'zaq12wsx');

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $content = [
            'notificationId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
            'notificationType' => NotificationType::ADMIN->value,
            'notificationUserType' => NotificationUserType::SYSTEM->value,
            'additionalData' => [
                'text' => 'Nowy text',
                'actionId' => $user1->getProposedAudiobooks()->getId(),
            ]
        ];

        self::$webClient->request('PATCH', '/api/admin/user/notification', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad notificationId
     * step 3 - Sending Request
     * step 4 - Checking response
     * @return void
     */
    public function test_adminUserNotificationPatchIncorrectActivate(): void
    {
        $notificationRepository = $this->getService(NotificationRepository::class);

        $this->assertInstanceOf(NotificationRepository::class, $notificationRepository);

        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest'], true, 'zaq12wsx', notActive: true);
        $user3 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123126', ['Guest', 'User'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category2, true);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1,
            $category2], active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd2', [$category2], active: true);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category2], active: true);

        $audiobook4 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd4', [$category4,
            $category2], active: true);
        $audiobook5 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd5', [$category5], active: true);
        $audiobook6 = $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd6', [$category5], active: true);

        $this->databaseMockManager->testFunc_addProposedAudiobooks($user1, $audiobook1);
        $this->databaseMockManager->testFunc_addProposedAudiobooks($user1, $audiobook2);
        $this->databaseMockManager->testFunc_addProposedAudiobooks($user1, $audiobook3);
        $this->databaseMockManager->testFunc_addProposedAudiobooks($user1, $audiobook4);
        $this->databaseMockManager->testFunc_addProposedAudiobooks($user1, $audiobook5);
        $this->databaseMockManager->testFunc_addProposedAudiobooks($user1, $audiobook6);

        $not1 = $this->databaseMockManager->testFunc_addNotifications([$user1,
            $user2,
            $user3], NotificationType::PROPOSED, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $content = [
            'notificationId'       => $not1->getId(),
            'notificationType'     => NotificationType::ADMIN->value,
            'notificationUserType' => NotificationUserType::SYSTEM->value,
            'additionalData'       => [
                'text'     => 'Nowy text',
                'actionId' => $user1->getId(),
                'active'   => false,
            ],
        ];

        self::$webClient->request('PATCH', '/api/admin/user/notification', server: [
            'HTTP_authorization' => $token->getToken(),
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }


    /**
     * step 1 - Preparing data
     * step 2 - Preparing JsonBodyContent with bad notificationId
     * step 3 - Sending Request
     * step 4 - Checking response
     *
     * @return void
     */
    public function test_adminUserNotificationPatchToAdminIncorrect(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123128', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user3 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123126', ['Guest', 'User'], true, 'zaq12wsx');

        $not1 = $this->databaseMockManager->testFunc_addNotifications([$user1, $user2, $user3], NotificationType::PROPOSED, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        $content = [
            'notificationId' => $not1->getId(),
            'notificationType' => NotificationType::ADMIN->value,
            'notificationUserType' => NotificationUserType::SYSTEM->value,
            'additionalData' => [
                'text' => 'Nowy text',
                'actionId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
            ]
        ];

        self::$webClient->request('PATCH', '/api/admin/user/notification', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(404);

        $this->responseTool->testErrorResponseData(self::$webClient);
    }

    public function test_adminUserNotificationPatchEmptyRequestData(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', notActive: true);
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123128', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [];

        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        self::$webClient->request('PATCH', '/api/admin/user/notification', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(400);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_adminUserNotificationPatchPermission(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User'], true, 'zaq12wsx', notActive: true);
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123128', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123127', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [
            'notificationId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
            'notificationType' => NotificationType::ADMIN->value,
            'notificationUserType' => NotificationUserType::SYSTEM->value,
            'additionalData' => [
                'text' => 'Nowy text',
                'actionId' => $user1->getProposedAudiobooks()->getId(),
            ]
        ];
        $token = $this->databaseMockManager->testFunc_loginUser($user1);

        self::$webClient->request('PATCH', '/api/admin/user/notification', server: [
            'HTTP_authorization' => $token->getToken()
        ], content: json_encode($content));

        self::assertResponseStatusCodeSame(403);

        $this->responseTool->testBadResponseData(self::$webClient);
    }

    public function test_adminUserNotificationPatchLogOut(): void
    {
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', notActive: true);
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123128', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123177', ['Guest', 'User'], true, 'zaq12wsx');

        $content = [
            'notificationId' => '66666c4e-16e6-1ecc-9890-a7e8b0073d3b',
            'notificationType' => NotificationType::ADMIN->value,
            'notificationUserType' => NotificationUserType::SYSTEM->value,
            'additionalData' => [
                'text' => 'Nowy text',
                'actionId' => $user1->getProposedAudiobooks()->getId(),
            ]
        ];

        self::$webClient->request('PATCH', '/api/admin/user/notification', content: json_encode($content));

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}