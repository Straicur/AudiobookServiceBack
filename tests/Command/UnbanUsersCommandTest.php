<?php

namespace App\Tests\Command;

use App\Repository\UserRepository;
use App\Tests\AbstractKernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class UnbanUsersCommandTest extends AbstractKernelTestCase
{
    public function test_userProposedAudiobooksSuccess()
    {
        $audiobookRepository = $this->getService(UserRepository::class);

        $this->assertInstanceOf(UserRepository::class, $audiobookRepository);

        /// step 1
        $user1 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");
        $user2 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx");
        $user3 = $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123123", ["Guest", "User"], true, "zaq12wsx", banned: true, bannedTo: (new \DateTime("Now"))->modify("- 1 month"));
     
        $cmd = $this->commandApplication->find("audiobookservice:users:unban");

        $tester = new CommandTester($cmd);

        $tester->execute([]);

        $tester->assertCommandIsSuccessful();
    }
}
