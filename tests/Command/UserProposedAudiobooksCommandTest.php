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
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123127", ["Guest", "User"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123128", ["Guest", "User"], true, "zaq12wsx");

        $category1 = $this->databaseMockManager->testFunc_addAudiobookCategory("1");
        $category2 = $this->databaseMockManager->testFunc_addAudiobookCategory("2", $category1);
        $category3 = $this->databaseMockManager->testFunc_addAudiobookCategory("3", $category1);
        $category4 = $this->databaseMockManager->testFunc_addAudiobookCategory("4", $category3);
        $category5 = $this->databaseMockManager->testFunc_addAudiobookCategory("5", $category2, true);
        $category6 = $this->databaseMockManager->testFunc_addAudiobookCategory("6", $category1);
        $category7 = $this->databaseMockManager->testFunc_addAudiobookCategory("7", $category1);
        $category8 = $this->databaseMockManager->testFunc_addAudiobookCategory("8", $category1);

        $audiobook1 = $this->databaseMockManager->testFunc_addAudiobook("t1", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d1", [$category1, $category2], null, (new \DateTime("Now"))->modify("- 1 month"), active: true);
        $audiobook2 = $this->databaseMockManager->testFunc_addAudiobook("t2", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d2", [$category2], active: true);
        $audiobook3 = $this->databaseMockManager->testFunc_addAudiobook("t3", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d3", [$category1, $category2], null, (new \DateTime("Now"))->modify("- 1 month"), active: true);
        $audiobook4 = $this->databaseMockManager->testFunc_addAudiobook("t4", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d4", [$category4], active: true);
        $audiobook5 = $this->databaseMockManager->testFunc_addAudiobook("t5", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d5", [$category2, $category3], null, (new \DateTime("Now"))->modify("- 1 month"), active: true);
        $audiobook6 = $this->databaseMockManager->testFunc_addAudiobook("t6", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d6", [$category8], active: true);
        $audiobook7 = $this->databaseMockManager->testFunc_addAudiobook("t7", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d7", [$category1, $category2], null, (new \DateTime("Now"))->modify("- 1 month"), active: true);
        $audiobook8 = $this->databaseMockManager->testFunc_addAudiobook("t8", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d8", [$category4], active: true);
        $audiobook9 = $this->databaseMockManager->testFunc_addAudiobook("t9", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d9", [$category1, $category2], null, (new \DateTime("Now"))->modify("- 1 month"), active: true);
        $audiobook10 = $this->databaseMockManager->testFunc_addAudiobook("t10", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d10", [$category4], active: true);
        $audiobook11 = $this->databaseMockManager->testFunc_addAudiobook("t11", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d11", [$category3, $category5], null, (new \DateTime("Now"))->modify("- 1 month"), active: true);
        $audiobook12 = $this->databaseMockManager->testFunc_addAudiobook("t12", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d12", [$category6], active: true);
        $audiobook13 = $this->databaseMockManager->testFunc_addAudiobook("t13", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d13", [$category1], null, (new \DateTime("Now"))->modify("- 1 month"), active: true);
        $audiobook14 = $this->databaseMockManager->testFunc_addAudiobook("t14", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d14", [$category2], active: true);
        $audiobook15 = $this->databaseMockManager->testFunc_addAudiobook("t15", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d15", [$category3, $category5], null, (new \DateTime("Now"))->modify("- 1 month"), active: true);
        $audiobook16 = $this->databaseMockManager->testFunc_addAudiobook("t16", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d16", [$category8], active: true);
        $audiobook17 = $this->databaseMockManager->testFunc_addAudiobook("t17", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d17", [$category7], active: true);
        $audiobook18 = $this->databaseMockManager->testFunc_addAudiobook("t18", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d18", [$category7, $category5], null, (new \DateTime("Now"))->modify("- 1 month"), active: true);
        $audiobook19 = $this->databaseMockManager->testFunc_addAudiobook("t19", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d19", [$category8], active: true);
        $audiobook20 = $this->databaseMockManager->testFunc_addAudiobook("t20", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d20", [$category1], active: true);
        $audiobook21 = $this->databaseMockManager->testFunc_addAudiobook("t21", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d21", [$category1, $category2], null, (new \DateTime("Now"))->modify("- 1 month"), active: true);
        $audiobook22 = $this->databaseMockManager->testFunc_addAudiobook("t22", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d22", [$category2], active: true);
        $audiobook23 = $this->databaseMockManager->testFunc_addAudiobook("t23", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d23", [$category1], active: true);
        $audiobook24 = $this->databaseMockManager->testFunc_addAudiobook("t24", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d24", [$category1, $category2], null, (new \DateTime("Now"))->modify("- 1 month"), active: true);
        $audiobook25 = $this->databaseMockManager->testFunc_addAudiobook("t25", "a", "2", "d", new \DateTime("Now"), 20, "20", 2, "desc", AudiobookAgeRange::ABOVE18, "d25", [$category2], active: true);

        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user1, $audiobook2, 1, 21);
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user1, $audiobook4, 1, 21);
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user1, $audiobook10, 1, 21);
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user1, $audiobook7, 1, 21);
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user1, $audiobook1, 1, 21);
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user1, $audiobook3, 1, 21);
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user1, $audiobook6, 1, 21);
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user1, $audiobook15, 1, 21);
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user1, $audiobook14, 1, 21);
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user1, $audiobook21, 1, 21);
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user1, $audiobook22, 1, 21);

        $this->databaseMockManager->testFunc_addMyList($user1, $audiobook1);
        $this->databaseMockManager->testFunc_addMyList($user1, $audiobook3);
        $this->databaseMockManager->testFunc_addMyList($user1, $audiobook6);
        $this->databaseMockManager->testFunc_addMyList($user1, $audiobook9);
        $this->databaseMockManager->testFunc_addMyList($user1, $audiobook11);
        $this->databaseMockManager->testFunc_addMyList($user1, $audiobook12);


        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user2, $audiobook2, 1, 21);
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user2, $audiobook4, 1, 21);
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user2, $audiobook10, 1, 21);
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user2, $audiobook7, 1, 21);
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user2, $audiobook1, 1, 21);
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user2, $audiobook3, 1, 21);
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user2, $audiobook6, 1, 21);
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user2, $audiobook15, 1, 21);
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user2, $audiobook14, 1, 21);
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user2, $audiobook21, 1, 21);
        $audiobookInfo = $this->databaseMockManager->testFunc_addAudiobookInfo($user2, $audiobook22, 1, 21);

        $this->databaseMockManager->testFunc_addMyList($user2, $audiobook3);
        $this->databaseMockManager->testFunc_addMyList($user2, $audiobook25);
        $this->databaseMockManager->testFunc_addMyList($user2, $audiobook23);
        $this->databaseMockManager->testFunc_addMyList($user2, $audiobook11);
        $this->databaseMockManager->testFunc_addMyList($user2, $audiobook12);

        $cmd = $this->commandApplication->find("audiobookservice:proposed:audiobooks");

        $tester = new CommandTester($cmd);

        $tester->execute([]);

        $tester->assertCommandIsSuccessful();
    }
}