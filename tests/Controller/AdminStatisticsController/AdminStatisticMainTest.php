<?php

declare(strict_types=1);

namespace App\Tests\Controller\AdminStatisticsController;

use App\Enums\AudiobookAgeRange;
use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Repository\UserRepository;
use App\Tests\AbstractWebTest;
use DateTime;

class AdminStatisticMainTest extends AbstractWebTest
{
    public function testAdminStatisticMainCorrect(): void
    {
        $userRepository = $this->getService(UserRepository::class);

        $this->assertInstanceOf(UserRepository::class, $userRepository);

        $admin = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test@cos.pl', '+48123123123', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');

        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123129', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', (new DateTime())->modify('-9 day'));
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx');
        $user3 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123126', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', notActive: true);
        $user4 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test4@cos.pl', '+48123123125', ['Guest', 'User', 'Administrator'], true, 'zaq12wsx', (new DateTime())->modify('-9 day'));
        $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test5@cos.pl', '+48123123124', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx', (new DateTime())->modify('-9 day'));

        $this->databaseMockManager->testFunc_loginUser($user1);
        $this->databaseMockManager->testFunc_loginUser($user2);

        $this->databaseMockManager->testFunc_addNotifications([$user1,$user2,$user3,$user4], NotificationType::ADMIN, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category1, true);

        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1,
            $category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd2', [$category2], active: true);
        $this->databaseMockManager->testFunc_addAudiobook('t', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category2]);

        $this->databaseMockManager->testFunc_addTechnicalBreak(false, $admin, (new DateTime())->modify('-6 day'), (new DateTime())->modify('-5 day'));

        $token = $this->databaseMockManager->testFunc_loginUser($admin);

        self::$webClient->request('GET', '/api/admin/statistic/main', server: [
            'HTTP_authorization' => $token->getToken()
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);

        $response = self::$webClient->getResponse();

        $responseContent = json_decode($response->getContent(), true);

        $this->assertIsArray($responseContent);

        $this->assertArrayHasKey('users', $responseContent);
        $this->assertArrayHasKey('categories', $responseContent);
        $this->assertArrayHasKey('audiobooks', $responseContent);
        $this->assertArrayHasKey('lastWeekRegistered', $responseContent);
        $this->assertArrayHasKey('lastWeekLogins', $responseContent);
        $this->assertArrayHasKey('lastWeekNotifications', $responseContent);
        $this->assertArrayHasKey('lastWeekTechnicalBreaks', $responseContent);

        $this->assertCount($responseContent['users'], $userRepository->findBy([
            'active' => true,
        ]));
        $this->assertSame($responseContent['categories'], 13);
        $this->assertSame($responseContent['audiobooks'], 2);
        $this->assertSame($responseContent['lastWeekLogins'], 3);
        $this->assertSame($responseContent['lastWeekNotifications'], 1);
        $this->assertSame($responseContent['lastWeekTechnicalBreaks'], 1);
    }

    public function testAdminStatisticMainLogout(): void
    {
        self::$webClient->request('GET', '/api/admin/statistic/main');

        self::assertResponseStatusCodeSame(401);

        $this->responseTool->testBadResponseData(self::$webClient);
    }
}
