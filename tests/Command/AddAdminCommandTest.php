<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Repository\UserInformationRepository;
use App\Tests\AbstractKernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class AddAdminCommandTest extends AbstractKernelTestCase
{
    public function testAddAdminCommandSuccess(): void
    {
        $userInformationRepository = $this->getService(UserInformationRepository::class);

        $this->assertInstanceOf(UserInformationRepository::class, $userInformationRepository);

        $cmd = $this->commandApplication->find('audiobookservice:admin:add');

        $tester = new CommandTester($cmd);

        $tester->execute([
            'firstname' => 'Damian',
            'lastname'  => 'Mosiński',
            'email'     => 'admin@audiobookbacktest.icu',
            'phone'     => '123412312',
            'password'  => 'zaq12wsx',
        ]);

        $tester->assertCommandIsSuccessful();

        $userInformationFound = $userInformationRepository->findOneBy(['email' => 'admin@audiobookbacktest.icu']);

        $this->assertNotNull($userInformationFound);
    }
}
