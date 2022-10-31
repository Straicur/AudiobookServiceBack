<?php

namespace App\Tests\Command;

use App\Enums\AudiobookAgeRange;
use App\Tests\AbstractKernelTestCase;
use DateTime;
use Symfony\Component\Console\Tester\CommandTester;

class UserProposedAudiobooksCommandTest extends AbstractKernelTestCase
{
    public function test_userProposedAudiobooksSuccess()
    {
        /// step 1
        $user = $this->databaseMockManager->testFunc_addUser("User", "Test", "test@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory("3", $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory("4", $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory("5", $category2, true);
        $category6 = $this->databaseMockManager->testFunc_addAudiobookCategory("6", $category1);
        $category7 = $this->databaseMockManager->testFunc_addAudiobookCategory("7", $category1);
        $category8 = $this->databaseMockManager->testFunc_addAudiobookCategory("8", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t1", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18,  "d1",  [$category1, $category2], null, (new \DateTime("Now"))->modify("- 1 month"), active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook("t2", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18,  "d2",  [$category2], active: true);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook("t3", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18,  "d3",  [$category1, $category2], null, (new \DateTime("Now"))->modify("- 1 month"), active: true);
        $audiobook4 = $this->databaseMockManager->testFunc_addAudiobook("t4", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18,  "d4",  [$category4], active: true);
        $audiobook5 = $this->databaseMockManager->testFunc_addAudiobook("t5", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18,  "d5",  [$category2, $category3], null, (new \DateTime("Now"))->modify("- 1 month"), active: true);
        $audiobook6 = $this->databaseMockManager->testFunc_addAudiobook("t6", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18,  "d6",  [$category8], active: true);
        $audiobook7 = $this->databaseMockManager->testFunc_addAudiobook("t1", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18,  "d7",  [$category1, $category2], null, (new \DateTime("Now"))->modify("- 1 month"), active: true);
        $audiobook8 = $this->databaseMockManager->testFunc_addAudiobook("t2", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18,  "d8",  [$category4], active: true);
        $audiobook9 = $this->databaseMockManager->testFunc_addAudiobook("t3", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18,  "d9",  [$category1, $category2], null, (new \DateTime("Now"))->modify("- 1 month"), active: true);
        $audiobook10 = $this->databaseMockManager->testFunc_addAudiobook("t4", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18,  "d10",  [$category4], active: true);
        $audiobook11 = $this->databaseMockManager->testFunc_addAudiobook("t5", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18,  "d11",  [$category3, $category5], null, (new \DateTime("Now"))->modify("- 1 month"), active: true);
        $audiobook12 = $this->databaseMockManager->testFunc_addAudiobook("t6", "a", "2", "d", new \DateTime("Now"), "20", "20", 2, "desc", AudiobookAgeRange::ABOVE18,  "d12",  [$category6], active: true);

        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook2,1,"dsa",new DateTime("Now"));
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook4,1,"dsa",new DateTime("Now"));
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook10,1,"dsa",new DateTime("Now"));
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook7,1,"dsa",new DateTime("Now"));
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook1,1,"dsa",new DateTime("Now"));
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook3,1,"dsa",new DateTime("Now"));
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user, $audiobook6,1,"dsa",new DateTime("Now"));
        $this->databaseMockManager->testFunc_addMyList($user,$audiobook1);
        $this->databaseMockManager->testFunc_addMyList($user,$audiobook3);
        $this->databaseMockManager->testFunc_addMyList($user,$audiobook6);
        $this->databaseMockManager->testFunc_addMyList($user,$audiobook9);
        $this->databaseMockManager->testFunc_addMyList($user,$audiobook11);
        $this->databaseMockManager->testFunc_addMyList($user,$audiobook12);

        $cmd = $this->commandApplication->find("audiobookservice:proposed:audiobooks");

        $tester = new CommandTester($cmd);

        $tester->execute([]);

        $tester->assertCommandIsSuccessful();
    }
}