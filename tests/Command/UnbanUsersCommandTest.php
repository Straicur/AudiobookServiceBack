<?php

namespace App\Tests\Command;

use App\Tests\AbstractKernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class UnbanUsersCommandTest extends AbstractKernelTestCase
{
    public function test_userProposedAudiobooksSuccess()
    {
        /// step 1
        $this->databaseMockManager->testFunc_addUser("User", "Test", "test1@cos.pl", "+48123123125", ["Guest", "User"], true, "zaq12wsx");
        $this->databaseMockManager->testFunc_addUser("User", "Test", "test2@cos.pl", "+48123123126", ["Guest", "User"], true, "zaq12wsx");
        $this->databaseMockManager->testFunc_addUser("User", "Test", "test3@cos.pl", "+48123123127", ["Guest", "User"], true, "zaq12wsx", banned: true, bannedTo: (new \DateTime("Now"))->modify("- 1 month"));
     
        $cmd = $this->commandApplication->find("audiobookservice:users:unban");

        $tester = new CommandTester($cmd);

        $tester->execute([]);

        $tester->assertCommandIsSuccessful();
    }
}
