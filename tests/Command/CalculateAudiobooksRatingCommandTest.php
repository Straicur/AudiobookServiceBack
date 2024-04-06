<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Enums\AudiobookAgeRange;
use App\Repository\AudiobookRepository;
use App\Service\AudiobookService;
use App\Tests\AbstractKernelTestCase;
use DateTime;
use Symfony\Component\Console\Tester\CommandTester;

class CalculateAudiobooksRatingCommandTest extends AbstractKernelTestCase
{
    public function test_userProposedAudiobooksSuccess()
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
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory('4', $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory('5', $category2, true);
        $category6 = $this->databaseMockManager->testFunc_addAudiobookCategory('6', $category1);
        $category7 = $this->databaseMockManager->testFunc_addAudiobookCategory('7', $category1);
        $category8 = $this->databaseMockManager->testFunc_addAudiobookCategory('8', $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook('t1', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd1', [$category1, $category2], null, (new DateTime())->modify('- 1 month'), active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook('t2', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd2', [$category2], active: true);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook('t3', 'a', '2', 'd', new DateTime(), 20, '20', 2, 'desc', AudiobookAgeRange::ABOVE18, 'd3', [$category1, $category2], null, (new DateTime())->modify('- 1 month'), active: true);

        $audiobookRating1 = $this->databaseMockManager->testFunc_addAudiobookRating($audiobook1, true, $user1);
        $audiobookRating1 = $this->databaseMockManager->testFunc_addAudiobookRating($audiobook1, true, $user2);
        $audiobookRating1 = $this->databaseMockManager->testFunc_addAudiobookRating($audiobook1, false, $user3);

        $audiobookRating1 = $this->databaseMockManager->testFunc_addAudiobookRating($audiobook2, true, $user1);
        $audiobookRating1 = $this->databaseMockManager->testFunc_addAudiobookRating($audiobook2, true, $user2);
        $audiobookRating1 = $this->databaseMockManager->testFunc_addAudiobookRating($audiobook2, false, $user3);

        $audiobookRating1 = $this->databaseMockManager->testFunc_addAudiobookRating($audiobook3, true, $user1);
        $audiobookRating1 = $this->databaseMockManager->testFunc_addAudiobookRating($audiobook3, true, $user2);
        $audiobookRating1 = $this->databaseMockManager->testFunc_addAudiobookRating($audiobook3, false, $user3);

        $cmd = $this->commandApplication->find('audiobookservice:calculate:rating');

        $tester = new CommandTester($cmd);

        $tester->execute([]);

        $tester->assertCommandIsSuccessful();

        $audiobook1After = $audiobookRepository->findOneBy([
            'id'=>$audiobook1->getId()
        ]);
        $audiobook2After = $audiobookRepository->findOneBy([
            'id'=>$audiobook2->getId()
        ]);
        $audiobook3After = $audiobookRepository->findOneBy([
            'id'=>$audiobook3->getId()
        ]);

        $this->assertNotSame(0,$audiobook1After->getAvgRating());
        $this->assertNotSame(0,$audiobook2After->getAvgRating());
        $this->assertNotSame(0,$audiobook3After->getAvgRating());
    }
}