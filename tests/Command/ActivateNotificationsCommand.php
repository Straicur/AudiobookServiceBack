<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Enums\NotificationType;
use App\Enums\NotificationUserType;
use App\Repository\NotificationRepository;
use App\Tests\AbstractKernelTestCase;
use DateTime;
use Symfony\Component\Console\Tester\CommandTester;

class ActivateNotificationsCommand extends AbstractKernelTestCase
{
    public function test_userProposedAudiobooksSuccess()
    {
        $notificationRepository = $this->getService(NotificationRepository::class);

        $this->assertInstanceOf(NotificationRepository::class, $notificationRepository);

        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123123', ['Guest',
            'User',
            'Administrator'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123127', ['Guest'], true, 'zaq12wsx', notActive: true);
        $user3 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123126', ['Guest', 'User'], true, 'zaq12wsx');

        $not1 = $this->databaseMockManager->testFunc_addNotifications([$user1,
            $user2,
            $user3], NotificationType::PROPOSED, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM, active: false, dateActive: (new DateTime())->modify('+1 day'));
        $not2 = $this->databaseMockManager->testFunc_addNotifications([$user1,
            $user2,
            $user3], NotificationType::PROPOSED, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM, active: false, dateActive: (new DateTime())->modify('-1 day'));

        $not3 = $this->databaseMockManager->testFunc_addNotifications([$user1,
            $user2,
            $user3], NotificationType::PROPOSED, $user1->getProposedAudiobooks()->getId(), NotificationUserType::SYSTEM);

        $cmd = $this->commandApplication->find('audiobookservice:notifications:activate');

        $tester = new CommandTester($cmd);

        $tester->execute([]);

        $tester->assertCommandIsSuccessful();

        $notification1After = $notificationRepository->find($not1->getId());
        $notification2After = $notificationRepository->find($not2->getId());
        $notification3After = $notificationRepository->find($not3->getId());

        $this->assertFalse($notification1After->isActive());
        $this->assertTrue($notification2After->isActive());
        $this->assertTrue($notification3After->isActive());
    }
}
