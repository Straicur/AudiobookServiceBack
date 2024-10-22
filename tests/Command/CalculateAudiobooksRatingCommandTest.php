<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Enums\AudiobookAgeRange;
use App\Repository\AudiobookRepository;
use App\Tests\AbstractKernelTestCase;
use DateTime;
use Symfony\Component\Console\Tester\CommandTester;

class CalculateAudiobooksRatingCommandTest extends AbstractKernelTestCase
{
    public function test_userProposedAudiobooksSuccess(): void
    {
        $audiobookRepository = $this->getService(AudiobookRepository::class);

        $this->assertInstanceOf(AudiobookRepository::class, $audiobookRepository);

        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test1@cos.pl', '+48123123124', ['Guest', 'User'], true, 'zaq12wsx');
        $user2 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test2@cos.pl', '+48123123125', ['Guest', 'User'], true, 'zaq12wsx');
        $user3 = $this->databaseMockManager->testFunc_addUser('User', 'Test', 'test3@cos.pl', '+48123123126', ['Guest', 'User'], true, 'zaq12wsx');

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory('1');
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory('2', $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory('3', $category1);
        $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category2, true);
        $this->databaseMockManager->testFunc_addAudiobookCategory('6', $category1);
        $this->databaseMockManager->testFunc_addAudiobookCategory('7', $category1);
        $this->databaseMockManager->testFunc_addAudiobookCategory('8', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t1', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], null, (new DateTime())->modify('- 1 month'), active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook('t2', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd2', [$category2], active: true);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook('t3', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category1, $category2], null, (new DateTime())->modify('- 1 month'), active: true);

        $this->databaseMockManager->testFunc_addAudiobookRating($audiobook1, 3, $user1);
        $this->databaseMockManager->testFunc_addAudiobookRating($audiobook1, 2, $user2);
        $this->databaseMockManager->testFunc_addAudiobookRating($audiobook1, 1, $user3);

        $this->databaseMockManager->testFunc_addAudiobookRating($audiobook2, 3, $user1);
        $this->databaseMockManager->testFunc_addAudiobookRating($audiobook2, 2, $user2);
        $this->databaseMockManager->testFunc_addAudiobookRating($audiobook2, 1, $user3);

        $this->databaseMockManager->testFunc_addAudiobookRating($audiobook3, 4, $user1);
        $this->databaseMockManager->testFunc_addAudiobookRating($audiobook3, 2, $user2);
        $this->databaseMockManager->testFunc_addAudiobookRating($audiobook3, 3, $user3);

        $cmd = $this->commandApplication->find('audiobookservice:calculate:rating');

        $tester = new CommandTester($cmd);

        $tester->execute([]);

        $tester->assertCommandIsSuccessful();

        $audiobook1After = $audiobookRepository->findOneBy([
            'id' => $audiobook1->getId(),
        ]);
        $audiobook2After = $audiobookRepository->findOneBy([
            'id' => $audiobook2->getId(),
        ]);
        $audiobook3After = $audiobookRepository->findOneBy([
            'id' => $audiobook3->getId(),
        ]);

        $this->assertSame(2, $audiobook1After->getAvgRating());
        $this->assertSame(2, $audiobook2After->getAvgRating());
        $this->assertSame(3, $audiobook3After->getAvgRating());
    }
}
